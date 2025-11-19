#!/bin/bash

###############################################################################
# DigiParc - Script de Maintenance
# Effectue les tâches de maintenance régulières
###############################################################################

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✓${NC} $1"
}

###############################################################################
# Maintenance Tasks
###############################################################################

cat <<'EOF'
╔══════════════════════════════════════════════════════════════╗
║              DigiParc - Script de Maintenance                ║
╚══════════════════════════════════════════════════════════════╝
EOF

echo ""

# 1. Nettoyage du cache
log "Nettoyage du cache..."
rm -rf storage/cache/*
success "Cache nettoyé"

# 2. Nettoyage des logs anciens
log "Nettoyage des logs anciens (>30 jours)..."
find var/log -name "*.log" -mtime +30 -delete
find /var/log/apache2 -name "digiparc-*.log.*" -mtime +30 -delete 2>/dev/null || true
success "Logs anciens supprimés"

# 3. Nettoyage des fichiers temporaires
log "Nettoyage des fichiers temporaires..."
find /tmp -name "php*" -mtime +1 -delete 2>/dev/null || true
find storage/temp -type f -mtime +7 -delete 2>/dev/null || true
success "Fichiers temporaires nettoyés"

# 4. Optimisation de la base de données
log "Optimisation de la base de données..."
if [ -f config/config.php ]; then
    DB_NAME=$(php -r "require 'config/config.php'; echo DB_NAME;")
    DB_USER=$(php -r "require 'config/config.php'; echo DB_USER;")
    DB_PASS=$(php -r "require 'config/config.php'; echo DB_PASS;")

    # Optimize tables
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        SELECT CONCAT('OPTIMIZE TABLE ', table_name, ';')
        FROM information_schema.tables
        WHERE table_schema='$DB_NAME'
    " | grep -v CONCAT | mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"

    success "Base de données optimisée"
fi

# 5. Rotation des sauvegardes
log "Rotation des sauvegardes..."
if [ -d storage/backups ]; then
    # Keep last 7 daily backups
    find storage/backups -name "daily_*.sql.gz" -mtime +7 -delete

    # Keep last 4 weekly backups
    find storage/backups -name "weekly_*.sql.gz" -mtime +28 -delete

    # Keep last 12 monthly backups
    find storage/backups -name "monthly_*.sql.gz" -mtime +365 -delete

    success "Sauvegardes anciennes supprimées"
fi

# 6. Vérification de l'espace disque
log "Vérification de l'espace disque..."
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')

if [ $DISK_USAGE -gt 90 ]; then
    echo "⚠️  ATTENTION: Espace disque faible ($DISK_USAGE%)"
elif [ $DISK_USAGE -gt 80 ]; then
    echo "⚠️  Espace disque: $DISK_USAGE% (surveiller)"
else
    success "Espace disque OK ($DISK_USAGE%)"
fi

# 7. Vérification des permissions
log "Vérification des permissions..."
chown -R www-data:www-data storage public/uploads var/log 2>/dev/null || true
chmod -R 775 storage public/uploads var/log 2>/dev/null || true
success "Permissions vérifiées"

# 8. Mise à jour du sitemap
log "Mise à jour du sitemap..."
php scripts/generate_sitemap.php 2>/dev/null || echo "  (sitemap generator not found, skipping)"

# 9. Statistiques
log "Génération des statistiques..."
echo ""
echo "  Base de données:"
if [ -f config/config.php ]; then
    echo "    - Taille: $(mysql -u "$DB_USER" -p"$DB_PASS" -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as 'Size (MB)' FROM information_schema.tables WHERE table_schema='$DB_NAME';" | tail -1) MB"
    echo "    - Tables: $(mysql -u "$DB_USER" -p"$DB_PASS" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" | tail -1)"
fi
echo ""
echo "  Fichiers:"
echo "    - Uploads: $(du -sh public/uploads 2>/dev/null | cut -f1)"
echo "    - Logs: $(du -sh var/log 2>/dev/null | cut -f1)"
echo "    - Cache: $(du -sh storage/cache 2>/dev/null | cut -f1)"
echo "    - Backups: $(du -sh storage/backups 2>/dev/null | cut -f1)"
echo ""

# 10. Logs de maintenance
MAINTENANCE_LOG="var/log/maintenance.log"
echo "[$(date +'%Y-%m-%d %H:%M:%S')] Maintenance completed successfully" >> "$MAINTENANCE_LOG"

echo ""
cat <<'EOF'
╔══════════════════════════════════════════════════════════════╗
║              ✓ Maintenance Terminée avec Succès              ║
╚══════════════════════════════════════════════════════════════╝
EOF
echo ""

success "Toutes les tâches de maintenance ont été effectuées"
echo ""
