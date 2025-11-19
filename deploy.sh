#!/bin/bash

###############################################################################
# Pakiparc Fleet Management - Script de Déploiement Production
# Version 3.0.0
#
# Ce script déploie automatiquement l'application sur un serveur de production
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
PROJECT_DIR=$(pwd)
BACKUP_DIR="/var/backups/pakiparc-deployments"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

###############################################################################
# Functions
###############################################################################

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✓${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
    exit 1
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

###############################################################################
# Pre-deployment Checks
###############################################################################

pre_deployment_checks() {
    log "Vérifications pré-déploiement..."

    # Check if in git repository
    if [ ! -d ".git" ]; then
        error "Ce n'est pas un dépôt Git"
    fi

    # Check if .env exists
    if [ ! -f ".env" ]; then
        error "Le fichier .env n'existe pas"
    fi

    # Check if database config exists
    if [ ! -f "config/config.php" ]; then
        error "Le fichier config/config.php n'existe pas"
    fi

    # Check if running as www-data or root
    if [ "$EUID" -ne 0 ] && [ "$(whoami)" != "www-data" ]; then
        error "Ce script doit être exécuté en tant que root ou www-data"
    fi

    success "Vérifications pré-déploiement terminées"
}

###############################################################################
# Backup Current Version
###############################################################################

backup_current_version() {
    log "Sauvegarde de la version actuelle..."

    mkdir -p "$BACKUP_DIR"

    # Backup files
    tar -czf "$BACKUP_DIR/pakiparc-$TIMESTAMP.tar.gz" \
        --exclude='.git' \
        --exclude='node_modules' \
        --exclude='vendor' \
        --exclude='storage/cache/*' \
        "$PROJECT_DIR"

    # Backup database
    if [ -f "config/config.php" ]; then
        DB_NAME=$(php -r "require 'config/config.php'; echo DB_NAME;")
        DB_USER=$(php -r "require 'config/config.php'; echo DB_USER;")
        DB_PASS=$(php -r "require 'config/config.php'; echo DB_PASS;")

        mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/pakiparc-db-$TIMESTAMP.sql.gz"
    fi

    success "Sauvegarde créée: $BACKUP_DIR/pakiparc-$TIMESTAMP.tar.gz"
}

###############################################################################
# Enable Maintenance Mode
###############################################################################

enable_maintenance_mode() {
    log "Activation du mode maintenance..."

    cat > public/maintenance.html <<'EOF'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - Pakiparc</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #fff;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        h1 { font-size: 3rem; margin-bottom: 1rem; }
        p { font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9; }
        .icon { font-size: 5rem; margin-bottom: 2rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔧</div>
        <h1>Maintenance en cours</h1>
        <p>Nous mettons à jour Pakiparc Fleet Management.<br>L'application sera de retour dans quelques minutes.</p>
        <p style="font-size: 0.9rem;">Merci de votre patience !</p>
    </div>
</body>
</html>
EOF

    # Redirect all traffic to maintenance page
    cat > public/.htaccess.maintenance <<'EOF'
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/maintenance.html$
RewriteRule ^(.*)$ /maintenance.html [R=503,L]
EOF

    mv public/.htaccess public/.htaccess.backup 2>/dev/null || true
    mv public/.htaccess.maintenance public/.htaccess

    success "Mode maintenance activé"
}

###############################################################################
# Disable Maintenance Mode
###############################################################################

disable_maintenance_mode() {
    log "Désactivation du mode maintenance..."

    mv public/.htaccess.backup public/.htaccess 2>/dev/null || true
    rm -f public/maintenance.html

    success "Mode maintenance désactivé"
}

###############################################################################
# Pull Latest Code
###############################################################################

pull_latest_code() {
    log "Récupération du code le plus récent..."

    # Stash any local changes
    git stash save "Auto-stash before deployment $TIMESTAMP" 2>/dev/null || true

    # Pull latest code
    CURRENT_BRANCH=$(git branch --show-current)
    git pull origin "$CURRENT_BRANCH"

    success "Code mis à jour"
}

###############################################################################
# Install/Update Dependencies
###############################################################################

update_dependencies() {
    log "Mise à jour des dépendances..."

    # Composer
    if [ -f "composer.json" ]; then
        composer install --no-dev --optimize-autoloader --no-interaction
        success "Dépendances Composer mises à jour"
    fi

    # NPM (if package.json exists)
    if [ -f "package.json" ]; then
        npm install --production
        npm run build 2>/dev/null || true
        success "Dépendances NPM mises à jour"
    fi
}

###############################################################################
# Run Database Migrations
###############################################################################

run_migrations() {
    log "Exécution des migrations de base de données..."

    if [ -d "database/migrations" ]; then
        DB_NAME=$(php -r "require 'config/config.php'; echo DB_NAME;")
        DB_USER=$(php -r "require 'config/config.php'; echo DB_USER;")
        DB_PASS=$(php -r "require 'config/config.php'; echo DB_PASS;")

        for migration in database/migrations/*.sql; do
            if [ -f "$migration" ]; then
                # Check if migration already applied (simple check)
                MIGRATION_NAME=$(basename "$migration")
                APPLIED=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sse "SELECT COUNT(*) FROM migrations WHERE name='$MIGRATION_NAME'" 2>/dev/null || echo "0")

                if [ "$APPLIED" = "0" ]; then
                    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration"

                    # Record migration
                    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "INSERT INTO migrations (name, applied_at) VALUES ('$MIGRATION_NAME', NOW())" 2>/dev/null || true

                    success "Migration $MIGRATION_NAME appliquée"
                fi
            fi
        done
    fi

    success "Migrations terminées"
}

###############################################################################
# Clear Cache
###############################################################################

clear_cache() {
    log "Nettoyage du cache..."

    # Clear application cache
    rm -rf storage/cache/*

    # Clear PHP opcache
    if command -v apache2 &> /dev/null; then
        systemctl reload apache2
    fi

    success "Cache nettoyé"
}

###############################################################################
# Set Permissions
###############################################################################

set_permissions() {
    log "Configuration des permissions..."

    # Set ownership
    chown -R www-data:www-data "$PROJECT_DIR"

    # Set directory permissions
    find "$PROJECT_DIR" -type d -exec chmod 755 {} \;
    find "$PROJECT_DIR" -type f -exec chmod 644 {} \;

    # Set writable directories
    chmod -R 775 public/uploads
    chmod -R 775 storage
    chmod -R 775 var/log

    # Make scripts executable
    chmod +x scripts/*.sh 2>/dev/null || true
    chmod +x app/cron/*.php 2>/dev/null || true

    success "Permissions configurées"
}

###############################################################################
# Restart Services
###############################################################################

restart_services() {
    log "Redémarrage des services..."

    # Apache
    if systemctl is-active --quiet apache2; then
        systemctl reload apache2
        success "Apache rechargé"
    fi

    # PHP-FPM (if installed)
    if systemctl is-active --quiet php8.1-fpm; then
        systemctl restart php8.1-fpm
        success "PHP-FPM redémarré"
    fi
}

###############################################################################
# Post-deployment Tests
###############################################################################

post_deployment_tests() {
    log "Tests post-déploiement..."

    # Test database connection
    if php -r "require 'config/config.php'; new PDO('mysql:host=localhost;dbname='.DB_NAME, DB_USER, DB_PASS);" 2>/dev/null; then
        success "Connexion base de données OK"
    else
        error "Échec connexion base de données"
    fi

    # Test web server
    if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|302"; then
        success "Serveur web OK"
    else
        warning "Serveur web pourrait avoir des problèmes"
    fi

    # Test writable directories
    if [ -w "public/uploads" ] && [ -w "storage" ]; then
        success "Permissions d'écriture OK"
    else
        error "Problème de permissions"
    fi

    success "Tests post-déploiement terminés"
}

###############################################################################
# Send Notification
###############################################################################

send_notification() {
    log "Envoi de notification..."

    # Log deployment
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] Deployment successful" >> var/log/deployments.log

    # Send email notification (if configured)
    # php scripts/notify_deployment.php 2>/dev/null || true

    success "Notification envoyée"
}

###############################################################################
# Rollback Function
###############################################################################

rollback() {
    error "Déploiement échoué! Rollback en cours..."

    # Restore files
    if [ -f "$BACKUP_DIR/pakiparc-$TIMESTAMP.tar.gz" ]; then
        tar -xzf "$BACKUP_DIR/pakiparc-$TIMESTAMP.tar.gz" -C /tmp/
        rsync -av --delete /tmp/$(basename $PROJECT_DIR)/ "$PROJECT_DIR/"
        success "Fichiers restaurés"
    fi

    # Restore database
    if [ -f "$BACKUP_DIR/pakiparc-db-$TIMESTAMP.sql.gz" ]; then
        gunzip < "$BACKUP_DIR/pakiparc-db-$TIMESTAMP.sql.gz" | mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"
        success "Base de données restaurée"
    fi

    disable_maintenance_mode
    restart_services

    error "Rollback terminé. Veuillez vérifier les logs."
}

###############################################################################
# Main Deployment Process
###############################################################################

main() {
    cat <<'EOF'
╔══════════════════════════════════════════════════════════════╗
║                   DÉPLOIEMENT PRODUCTION                     ║
║                  Pakiparc Fleet Management                   ║
╚══════════════════════════════════════════════════════════════╝
EOF

    echo ""

    # Set up error handling
    trap rollback ERR

    # Execute deployment steps
    pre_deployment_checks
    backup_current_version
    enable_maintenance_mode
    pull_latest_code
    update_dependencies
    run_migrations
    clear_cache
    set_permissions
    disable_maintenance_mode
    restart_services
    post_deployment_tests
    send_notification

    # Success
    cat <<'EOF'

╔══════════════════════════════════════════════════════════════╗
║              ✓ DÉPLOIEMENT RÉUSSI !                         ║
╚══════════════════════════════════════════════════════════════╝
EOF

    echo ""
    success "Application déployée avec succès!"
    log "Backup disponible: $BACKUP_DIR/pakiparc-$TIMESTAMP.tar.gz"
    echo ""
}

# Run deployment
main "$@"
