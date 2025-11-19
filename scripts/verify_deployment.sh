#!/bin/bash

###############################################################################
# Pakiparc - Script de Vérification Post-Déploiement
# Vérifie que TOUT fonctionne correctement après le déploiement
###############################################################################

set -e

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

ERRORS=0
WARNINGS=0

check() {
    local name="$1"
    local command="$2"

    echo -n "Vérification: $name... "

    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC}"
        return 0
    else
        echo -e "${RED}✗${NC}"
        ERRORS=$((ERRORS + 1))
        return 1
    fi
}

warn() {
    echo -e "${YELLOW}⚠ $1${NC}"
    WARNINGS=$((WARNINGS + 1))
}

###############################################################################
# Vérifications
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Pakiparc - Vérification Post-Déploiement"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. Fichiers essentiels
echo "📁 Vérification des fichiers..."
check "Fichier .env existe" "[ -f .env ]"
check "Fichier config.php existe" "[ -f config/config.php ]"
check "Dossier vendor existe" "[ -d vendor ]"
check "Dossier public existe" "[ -d public ]"
check "Dossier uploads accessible" "[ -w public/uploads ]"
check "Dossier storage accessible" "[ -w storage ]"
echo ""

# 2. Base de données
echo "🗄️  Vérification de la base de données..."
if [ -f config/config.php ]; then
    DB_NAME=$(php -r "require 'config/config.php'; echo DB_NAME;" 2>/dev/null || echo "")
    DB_USER=$(php -r "require 'config/config.php'; echo DB_USER;" 2>/dev/null || echo "")
    DB_PASS=$(php -r "require 'config/config.php'; echo DB_PASS;" 2>/dev/null || echo "")

    check "Connexion base de données" "php -r \"new PDO('mysql:host=localhost;dbname=$DB_NAME', '$DB_USER', '$DB_PASS');\""
    check "Table users existe" "mysql -u $DB_USER -p$DB_PASS $DB_NAME -e 'SELECT 1 FROM users LIMIT 1'"
    check "Table companies existe" "mysql -u $DB_USER -p$DB_PASS $DB_NAME -e 'SELECT 1 FROM companies LIMIT 1'"
    check "Table vehicles existe" "mysql -u $DB_USER -p$DB_PASS $DB_NAME -e 'SELECT 1 FROM vehicles LIMIT 1'"
fi
echo ""

# 3. Serveur web
echo "🌐 Vérification du serveur web..."
check "Apache actif" "systemctl is-active --quiet apache2 || systemctl is-active --quiet nginx"
check "Port 80 ouvert" "netstat -tuln | grep -q ':80 '"

# Test HTTP response
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|302"; then
    echo -e "Vérification: Réponse HTTP... ${GREEN}✓${NC}"
else
    echo -e "Vérification: Réponse HTTP... ${RED}✗${NC}"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# 4. PHP
echo "🐘 Vérification de PHP..."
check "PHP installé" "command -v php"
check "Version PHP >= 8.1" "php -r 'exit(version_compare(PHP_VERSION, \"8.1.0\", \">=\") ? 0 : 1);'"
check "Extension PDO" "php -m | grep -q pdo_mysql"
check "Extension mbstring" "php -m | grep -q mbstring"
check "Extension gd" "php -m | grep -q gd"
check "Extension curl" "php -m | grep -q curl"
echo ""

# 5. Permissions
echo "🔐 Vérification des permissions..."
check "uploads/ writable" "[ -w public/uploads ]"
check "storage/ writable" "[ -w storage ]"
check "logs/ writable" "[ -w var/log ]"
check "cache/ writable" "[ -w storage/cache ]"
echo ""

# 6. Services externes (optionnel)
echo "🔧 Vérification des services externes..."
if grep -q "TWILIO_ACCOUNT_SID=" .env && [ -n "$(grep TWILIO_ACCOUNT_SID= .env | cut -d'=' -f2)" ]; then
    echo -e "  Twilio configuré: ${GREEN}✓${NC}"
else
    warn "Twilio non configuré (SMS/WhatsApp indisponibles)"
fi

if grep -q "STRIPE_SECRET_KEY=" .env && [ -n "$(grep STRIPE_SECRET_KEY= .env | cut -d'=' -f2)" ]; then
    echo -e "  Stripe configuré: ${GREEN}✓${NC}"
else
    warn "Stripe non configuré (paiements indisponibles)"
fi

if grep -q "SMTP_HOST=" .env && [ -n "$(grep SMTP_HOST= .env | cut -d'=' -f2)" ]; then
    echo -e "  SMTP configuré: ${GREEN}✓${NC}"
else
    warn "SMTP non configuré (emails indisponibles)"
fi
echo ""

# 7. CRON jobs
echo "⏰ Vérification des CRON jobs..."
if crontab -l 2>/dev/null | grep -q "pakiparc"; then
    echo -e "  CRON jobs configurés: ${GREEN}✓${NC}"
else
    warn "CRON jobs non configurés"
fi
echo ""

# 8. Sécurité
echo "🛡️  Vérification de la sécurité..."
check "DEBUG désactivé" "grep -q 'DEBUG.*false' config/config.php"
check "Fichiers sensibles protégés" "[ ! -r .env ] || [ $(stat -c '%a' .env) -le 600 ]"

if grep -q "APP_ENV=production" .env; then
    echo -e "  Mode production: ${GREEN}✓${NC}"
else
    warn "Mode production non activé dans .env"
fi
echo ""

# 9. Test de performance
echo "⚡ Test de performance..."
START=$(date +%s%N)
curl -s -o /dev/null http://localhost
END=$(date +%s%N)
RESPONSE_TIME=$(( (END - START) / 1000000 ))

echo "  Temps de réponse: ${RESPONSE_TIME}ms"
if [ $RESPONSE_TIME -lt 500 ]; then
    echo -e "  Performance: ${GREEN}Excellente${NC}"
elif [ $RESPONSE_TIME -lt 1000 ]; then
    echo -e "  Performance: ${GREEN}Bonne${NC}"
else
    echo -e "  Performance: ${YELLOW}À améliorer${NC}"
fi
echo ""

# 10. Logs
echo "📋 Vérification des logs..."
if [ -d var/log ]; then
    LOG_COUNT=$(find var/log -name "*.log" -type f | wc -l)
    echo "  Fichiers de log: $LOG_COUNT"

    ERROR_COUNT=$(find var/log -name "*.log" -type f -exec grep -i "error\|fatal" {} \; 2>/dev/null | wc -l)
    if [ $ERROR_COUNT -gt 0 ]; then
        warn "Erreurs détectées dans les logs ($ERROR_COUNT)"
    fi
fi
echo ""

###############################################################################
# Résumé
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  RÉSUMÉ DE LA VÉRIFICATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✓ PARFAIT ! Aucun problème détecté.${NC}"
    echo ""
    echo "🎉 L'application est prête pour la production !"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠ $WARNINGS avertissement(s) détecté(s).${NC}"
    echo ""
    echo "L'application fonctionne mais certaines fonctionnalités peuvent être limitées."
    exit 0
else
    echo -e "${RED}✗ $ERRORS erreur(s) critique(s) détectée(s).${NC}"
    if [ $WARNINGS -gt 0 ]; then
        echo -e "${YELLOW}⚠ $WARNINGS avertissement(s) également détecté(s).${NC}"
    fi
    echo ""
    echo "❌ Veuillez corriger les erreurs avant de mettre en production."
    exit 1
fi
