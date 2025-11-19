#!/bin/bash

###############################################################################
# DigiParc Fleet Management - Installation Automatique
# Version 3.0.0 - Production Ready
#
# Ce script installe TOUT automatiquement en 5 minutes !
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR=$(pwd)
DB_NAME="digiparc"
DB_USER="digiparc_user"
WEB_USER="www-data"

###############################################################################
# Helper Functions
###############################################################################

print_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Ce script doit être exécuté en tant que root"
        print_info "Utilisez: sudo bash install.sh"
        exit 1
    fi
}

###############################################################################
# Step 1: System Requirements Check
###############################################################################

check_requirements() {
    print_header "Vérification des prérequis système"

    local missing_packages=()

    # Check PHP
    if ! command -v php &> /dev/null; then
        missing_packages+=("php8.1")
    else
        PHP_VERSION=$(php -r 'echo PHP_VERSION;')
        print_success "PHP $PHP_VERSION installé"
    fi

    # Check MySQL/MariaDB
    if ! command -v mysql &> /dev/null; then
        missing_packages+=("mysql-server")
    else
        print_success "MySQL/MariaDB installé"
    fi

    # Check Apache
    if ! command -v apache2 &> /dev/null; then
        missing_packages+=("apache2")
    else
        print_success "Apache2 installé"
    fi

    # Check Composer
    if ! command -v composer &> /dev/null; then
        print_warning "Composer non installé - sera installé"
    else
        print_success "Composer installé"
    fi

    # Install missing packages
    if [ ${#missing_packages[@]} -gt 0 ]; then
        print_warning "Installation des paquets manquants..."
        apt-get update
        apt-get install -y "${missing_packages[@]}"
    fi
}

###############################################################################
# Step 2: Install PHP Extensions
###############################################################################

install_php_extensions() {
    print_header "Installation des extensions PHP requises"

    local extensions=(
        "php8.1-mysql"
        "php8.1-mbstring"
        "php8.1-curl"
        "php8.1-gd"
        "php8.1-xml"
        "php8.1-zip"
        "php8.1-intl"
    )

    apt-get install -y "${extensions[@]}"

    print_success "Extensions PHP installées"
}

###############################################################################
# Step 3: Install Composer
###############################################################################

install_composer() {
    print_header "Installation de Composer"

    if command -v composer &> /dev/null; then
        print_info "Composer déjà installé"
        return
    fi

    cd /tmp
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"

    cd "$PROJECT_DIR"

    print_success "Composer installé"
}

###############################################################################
# Step 4: Install Dependencies
###############################################################################

install_dependencies() {
    print_header "Installation des dépendances PHP"

    if [ ! -f "composer.json" ]; then
        cat > composer.json <<'EOF'
{
    "name": "digiparc/fleet-management",
    "description": "DigiParc Fleet Management System",
    "type": "project",
    "require": {
        "php": ">=8.1",
        "phpmailer/phpmailer": "^6.8",
        "stripe/stripe-php": "^13.0",
        "twilio/sdk": "^7.0",
        "phpoffice/phpspreadsheet": "^1.29"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
EOF
    fi

    composer install --no-dev --optimize-autoloader

    print_success "Dépendances installées"
}

###############################################################################
# Step 5: Database Setup
###############################################################################

setup_database() {
    print_header "Configuration de la base de données"

    read -p "Nom de la base de données [$DB_NAME]: " input_db_name
    DB_NAME=${input_db_name:-$DB_NAME}

    read -p "Utilisateur de la base de données [$DB_USER]: " input_db_user
    DB_USER=${input_db_user:-$DB_USER}

    read -sp "Mot de passe de la base de données: " DB_PASS
    echo ""

    read -sp "Mot de passe root MySQL: " MYSQL_ROOT_PASS
    echo ""

    print_info "Création de la base de données..."

    mysql -u root -p"$MYSQL_ROOT_PASS" <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

    print_success "Base de données créée"

    # Import schema
    if [ -f "database/schema.sql" ]; then
        print_info "Import du schéma de base de données..."
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql
        print_success "Schéma importé"
    fi

    # Run migrations
    if [ -d "database/migrations" ]; then
        print_info "Exécution des migrations..."
        for migration in database/migrations/*.sql; do
            if [ -f "$migration" ]; then
                mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration"
                print_success "Migration $(basename $migration) exécutée"
            fi
        done
    fi
}

###############################################################################
# Step 6: Environment Configuration
###############################################################################

configure_environment() {
    print_header "Configuration de l'environnement"

    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_success "Fichier .env créé"
    fi

    # Generate JWT secret
    JWT_SECRET=$(openssl rand -base64 32)

    # Update .env file
    sed -i "s/DB_NAME=.*/DB_NAME=$DB_NAME/" .env
    sed -i "s/DB_USER=.*/DB_USER=$DB_USER/" .env
    sed -i "s/DB_PASS=.*/DB_PASS=$DB_PASS/" .env
    sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
    sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
    sed -i "s/DEBUG=.*/DEBUG=false/" .env

    print_success "Configuration de l'environnement mise à jour"

    # Create config.php from .env
    if [ ! -f "config/config.php" ]; then
        cat > config/config.php <<EOF
<?php
// Auto-generated configuration file
define('DB_HOST', 'localhost');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');
define('JWT_SECRET', '$JWT_SECRET');
define('APP_ENV', 'production');
define('DEBUG', false);
define('APP_NAME', 'DigiParc Fleet Management');
define('APP_VERSION', '3.0.0');
define('APP_URL', 'http://$(hostname -I | awk '{print $1}')');
EOF
        print_success "Fichier config/config.php créé"
    fi
}

###############################################################################
# Step 7: File Permissions
###############################################################################

set_permissions() {
    print_header "Configuration des permissions"

    # Create required directories
    mkdir -p public/uploads/{logos,documents,vehicles,drivers}
    mkdir -p storage/{logs,cache,backups}
    mkdir -p var/log

    # Set ownership
    chown -R $WEB_USER:$WEB_USER "$PROJECT_DIR"

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

    print_success "Permissions configurées"
}

###############################################################################
# Step 8: Apache Configuration
###############################################################################

configure_apache() {
    print_header "Configuration d'Apache"

    # Enable required modules
    a2enmod rewrite
    a2enmod ssl
    a2enmod headers

    # Create virtual host
    DOMAIN=$(hostname -f)

    cat > /etc/apache2/sites-available/digiparc.conf <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAdmin admin@$DOMAIN
    DocumentRoot $PROJECT_DIR/public

    <Directory $PROJECT_DIR/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Security headers
        Header set X-Content-Type-Options "nosniff"
        Header set X-Frame-Options "SAMEORIGIN"
        Header set X-XSS-Protection "1; mode=block"
    </Directory>

    # Logging
    ErrorLog \${APACHE_LOG_DIR}/digiparc-error.log
    CustomLog \${APACHE_LOG_DIR}/digiparc-access.log combined

    # PHP settings
    php_value upload_max_filesize 10M
    php_value post_max_size 12M
    php_value memory_limit 256M
    php_value max_execution_time 300
</VirtualHost>
EOF

    # Enable site
    a2dissite 000-default.conf 2>/dev/null || true
    a2ensite digiparc.conf

    # Restart Apache
    systemctl restart apache2

    print_success "Apache configuré"
}

###############################################################################
# Step 9: CRON Jobs Setup
###############################################################################

setup_cron_jobs() {
    print_header "Configuration des tâches CRON"

    # Create crontab entries
    (crontab -l 2>/dev/null || true; cat <<EOF

# DigiParc Fleet Management - Tâches automatiques
# Vérification des alertes toutes les heures
0 * * * * php $PROJECT_DIR/app/cron/check_alerts.php >> $PROJECT_DIR/var/log/cron-alerts.log 2>&1

# Sauvegarde quotidienne à 2h du matin
0 2 * * * php $PROJECT_DIR/app/cron/daily_backup.php >> $PROJECT_DIR/var/log/cron-backup.log 2>&1

# Sauvegarde hebdomadaire des fichiers le dimanche à 3h
0 3 * * 0 php $PROJECT_DIR/app/cron/weekly_file_backup.php >> $PROJECT_DIR/var/log/cron-backup.log 2>&1

# Nettoyage des fichiers temporaires quotidien à 4h
0 4 * * * find $PROJECT_DIR/storage/cache -type f -mtime +7 -delete

# Archivage des anciennes alertes toutes les semaines
0 5 * * 1 php $PROJECT_DIR/app/cron/archive_old_alerts.php >> $PROJECT_DIR/var/log/cron-alerts.log 2>&1

EOF
) | crontab -

    print_success "Tâches CRON configurées"
}

###############################################################################
# Step 10: Create Super Admin
###############################################################################

create_super_admin() {
    print_header "Création du compte Super Admin"

    read -p "Email du Super Admin [admin@digiparc.com]: " ADMIN_EMAIL
    ADMIN_EMAIL=${ADMIN_EMAIL:-admin@digiparc.com}

    read -sp "Mot de passe du Super Admin: " ADMIN_PASS
    echo ""

    ADMIN_PASS_HASH=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_BCRYPT);")

    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
INSERT INTO users (email, password, first_name, last_name, role, status, created_at)
VALUES ('$ADMIN_EMAIL', '$ADMIN_PASS_HASH', 'Super', 'Admin', 'super_admin', 'active', NOW())
ON DUPLICATE KEY UPDATE password = '$ADMIN_PASS_HASH';
EOF

    print_success "Compte Super Admin créé"
}

###############################################################################
# Step 11: Security Hardening
###############################################################################

security_hardening() {
    print_header "Durcissement de la sécurité"

    # Disable directory listing
    echo "Options -Indexes" > "$PROJECT_DIR/public/.htaccess"

    # Protect sensitive files
    cat >> "$PROJECT_DIR/public/.htaccess" <<'EOF'

# Prevent access to sensitive files
<FilesMatch "\.(env|ini|log|sql)$">
    Require all denied
</FilesMatch>

# Prevent access to hidden files
<FilesMatch "^\.">
    Require all denied
</FilesMatch>

# Enable rewrite engine
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]
EOF

    # Set secure PHP configuration
    cat >> /etc/php/8.1/apache2/conf.d/99-digiparc.ini <<EOF
; DigiParc Security Configuration
expose_php = Off
display_errors = Off
log_errors = On
error_log = $PROJECT_DIR/var/log/php-error.log
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
EOF

    systemctl reload apache2

    print_success "Sécurité renforcée"
}

###############################################################################
# Step 12: Final Checks
###############################################################################

final_checks() {
    print_header "Vérifications finales"

    # Check database connection
    if php -r "
        \$db = new PDO('mysql:host=localhost;dbname=$DB_NAME', '$DB_USER', '$DB_PASS');
        echo 'OK';
    " 2>/dev/null | grep -q "OK"; then
        print_success "Connexion à la base de données OK"
    else
        print_error "Échec de la connexion à la base de données"
    fi

    # Check web server
    if systemctl is-active --quiet apache2; then
        print_success "Apache en cours d'exécution"
    else
        print_error "Apache n'est pas en cours d'exécution"
    fi

    # Check file permissions
    if [ -w "public/uploads" ]; then
        print_success "Permissions d'écriture OK"
    else
        print_error "Permissions d'écriture manquantes"
    fi

    # Check CRON jobs
    if crontab -l | grep -q "digiparc"; then
        print_success "Tâches CRON configurées"
    else
        print_warning "Tâches CRON non configurées"
    fi
}

###############################################################################
# Main Installation Process
###############################################################################

main() {
    clear

    cat <<'EOF'
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║        ╔═╗  ┬  ┌─┐  ┬  ╔═╗  ┌─┐  ┬─┐  ┌─┐                ║
║        ║  │ │  │ ┬  │  ╠═╝  ├─┤  ├┬┘  │                   ║
║        ╚═╝  ┴  └─┘  ┴  ╩    ┴ ┴  ┴└─  └─┘                ║
║                                                              ║
║            Fleet Management System v3.0.0                   ║
║                 Installation Automatique                     ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
EOF

    echo ""
    print_info "Ce script va installer DigiParc Fleet Management System"
    print_warning "L'installation prendra environ 5-10 minutes"
    echo ""
    read -p "Voulez-vous continuer? (o/N): " -n 1 -r
    echo ""

    if [[ ! $REPLY =~ ^[OoYy]$ ]]; then
        print_error "Installation annulée"
        exit 1
    fi

    check_root
    check_requirements
    install_php_extensions
    install_composer
    install_dependencies
    setup_database
    configure_environment
    set_permissions
    configure_apache
    setup_cron_jobs
    create_super_admin
    security_hardening
    final_checks

    # Success message
    clear
    cat <<'EOF'
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║                  ✓ INSTALLATION RÉUSSIE !                   ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
EOF

    echo ""
    print_success "DigiParc Fleet Management est maintenant installé!"
    echo ""
    print_info "Accédez à votre application:"
    echo -e "  ${GREEN}http://$(hostname -I | awk '{print $1}')${NC}"
    echo ""
    print_info "Connectez-vous avec:"
    echo -e "  Email: ${YELLOW}$ADMIN_EMAIL${NC}"
    echo -e "  Mot de passe: ${YELLOW}(celui que vous avez défini)${NC}"
    echo ""
    print_warning "IMPORTANT: Configurez vos services externes dans .env:"
    echo "  - Twilio (SMS & WhatsApp)"
    echo "  - Stripe (Paiements)"
    echo "  - SMTP (Email)"
    echo "  - Google Maps API"
    echo ""
    print_info "Documentation: $PROJECT_DIR/README.md"
    echo ""
}

# Run installation
main "$@"
