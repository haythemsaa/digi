# 🚀 Déploiement Rapide - Pakiparc Fleet Management v3.0.0

## Installation EN 5 MINUTES ! ⚡

### Option 1: Installation Automatique (RECOMMANDÉE)

```bash
# 1. Cloner le projet
git clone https://github.com/haythemsaa/digi.git
cd digi

# 2. Lancer l'installation automatique
sudo bash install.sh
```

C'est TOUT ! Le script fait TOUT automatiquement ! ✅

---

### Option 2: Installation Manuelle Rapide

```bash
# 1. Cloner le projet
git clone https://github.com/haythemsaa/digi.git
cd digi

# 2. Installer les dépendances
sudo apt-get update
sudo apt-get install -y apache2 mysql-server php8.1 php8.1-mysql php8.1-mbstring \
    php8.1-curl php8.1-gd php8.1-xml php8.1-zip php8.1-intl composer

# 3. Configurer la base de données
sudo mysql -e "CREATE DATABASE pakiparc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'pakiparc_user'@'localhost' IDENTIFIED BY 'VotreMotDePasse';"
sudo mysql -e "GRANT ALL ON pakiparc.* TO 'pakiparc_user'@'localhost';"
sudo mysql pakiparc < database/schema.sql

# 4. Configurer l'application
cp .env.example .env
nano .env  # Modifier DB_NAME, DB_USER, DB_PASS

# 5. Installer dépendances PHP
composer install --no-dev --optimize-autoloader

# 6. Configurer Apache
sudo cp config/apache/pakiparc.conf /etc/apache2/sites-available/
sudo a2ensite pakiparc
sudo a2enmod rewrite
sudo systemctl restart apache2

# 7. Permissions
sudo chown -R www-data:www-data /var/www/html/digi
sudo chmod -R 775 public/uploads storage var/log

# 8. CRON jobs
sudo crontab -e
# Ajouter:
0 * * * * php /var/www/html/digi/app/cron/check_alerts.php
0 2 * * * php /var/www/html/digi/app/cron/daily_backup.php
```

---

## 🌐 Déploiement sur un Serveur de Production

### VPS/Serveur Dédié (Ubuntu/Debian)

```bash
# Se connecter au serveur
ssh root@votre-serveur.com

# Cloner et installer
cd /var/www/html
git clone https://github.com/haythemsaa/digi.git
cd digi
sudo bash install.sh

# Configurer le domaine
sudo nano /etc/apache2/sites-available/pakiparc.conf
# Modifier ServerName avec votre domaine

# Redémarrer Apache
sudo systemctl restart apache2
```

### Hébergement Partagé (cPanel)

1. **Télécharger le ZIP** depuis GitHub
2. **Uploader** via File Manager dans `public_html/`
3. **Extraire** le ZIP
4. **Créer la base de données** via cPanel MySQL Databases
5. **Importer** `database/schema.sql` via phpMyAdmin
6. **Configurer** `.env` avec les infos de la base
7. **Configurer** le document root vers `/public_html/digi/public`

### Docker (Le plus rapide !)

```bash
# Avec Docker Compose
git clone https://github.com/haythemsaa/digi.git
cd digi
docker-compose up -d

# Accéder à: http://localhost
```

---

## ⚙️ Configuration Post-Installation

### 1. Services Externes à Configurer

Éditez `.env` et ajoutez vos clés API :

```bash
# SMS & WhatsApp (Twilio)
TWILIO_ACCOUNT_SID=votre_sid
TWILIO_AUTH_TOKEN=votre_token
TWILIO_PHONE_NUMBER=+33123456789

# Paiements (Stripe)
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLIC_KEY=pk_live_...

# Email (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=votre@email.com
SMTP_PASSWORD=votre_mot_de_passe

# Google Maps
GOOGLE_MAPS_API_KEY=votre_cle_api
```

### 2. Configurer SSL (HTTPS)

```bash
# Avec Let's Encrypt (Gratuit!)
sudo apt-get install -y certbot python3-certbot-apache
sudo certbot --apache -d votre-domaine.com
```

### 3. Optimisation Production

```bash
# Activer le cache PHP OPcache
sudo nano /etc/php/8.1/apache2/conf.d/10-opcache.ini
# Ajouter:
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Redémarrer
sudo systemctl restart apache2
```

---

## 🔐 Sécurité Post-Installation

### Checklist de Sécurité

- [ ] Changer le mot de passe Super Admin par défaut
- [ ] Activer HTTPS avec SSL
- [ ] Configurer le firewall UFW
- [ ] Désactiver les fonctions PHP dangereuses
- [ ] Configurer fail2ban
- [ ] Mettre à jour régulièrement

```bash
# Firewall basique
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable

# Fail2ban
sudo apt-get install -y fail2ban
sudo systemctl enable fail2ban
```

---

## 📊 Monitoring & Maintenance

### Vérifier le Statut

```bash
# Services
sudo systemctl status apache2
sudo systemctl status mysql

# Logs
tail -f var/log/php-error.log
tail -f /var/log/apache2/pakiparc-error.log

# Espace disque
df -h

# Sauvegardes
ls -lh /var/backups/pakiparc/
```

### Maintenance Régulière

```bash
# Mise à jour de l'application
cd /var/www/html/digi
sudo bash deploy.sh

# Nettoyage des logs
find var/log -name "*.log" -mtime +30 -delete

# Vérifier les sauvegardes
ls -lh storage/backups/
```

---

## 🆘 Dépannage Rapide

### Problème: Page blanche

```bash
# Activer les erreurs temporairement
sudo nano config/config.php
# Changer: define('DEBUG', true);

# Vérifier les logs
tail -f var/log/php-error.log
```

### Problème: Erreur 500

```bash
# Vérifier les permissions
sudo chown -R www-data:www-data /var/www/html/digi
sudo chmod -R 775 public/uploads storage var/log

# Vérifier Apache
sudo apache2ctl configtest
```

### Problème: Base de données inaccessible

```bash
# Tester la connexion
mysql -u pakiparc_user -p pakiparc

# Vérifier les credentials dans .env
cat .env | grep DB_
```

### Problème: CRON jobs ne s'exécutent pas

```bash
# Vérifier les CRON
crontab -l

# Tester manuellement
php app/cron/check_alerts.php

# Vérifier les logs
tail -f var/log/cron-alerts.log
```

---

## 🎯 Accès à l'Application

### URL par Défaut

- **Application Web**: `http://votre-serveur/`
- **API REST**: `http://votre-serveur/api/v1/`
- **Documentation API**: `http://votre-serveur/api/v1/docs/`

### Identifiants par Défaut

- **Email**: `admin@pakiparc.com`
- **Mot de passe**: Celui que vous avez défini lors de l'installation

⚠️ **IMPORTANT**: Changez le mot de passe immédiatement après la première connexion !

---

## 📞 Support

### Documentation

- [README.md](README.md) - Documentation complète
- [IMPROVEMENT_PLAN.md](IMPROVEMENT_PLAN.md) - Plan d'amélioration
- [API Documentation](api/v1/docs/openapi.yaml) - OpenAPI 3.0

### Obtenir de l'Aide

- **Issues GitHub**: https://github.com/haythemsaa/digi/issues
- **Email**: support@pakiparc.com
- **Documentation en ligne**: https://docs.pakiparc.com

---

## ✅ Checklist de Déploiement

- [ ] Serveur configuré (Ubuntu/Debian)
- [ ] PHP 8.1+ installé
- [ ] MySQL 8.0+ installé
- [ ] Apache2 installé et configuré
- [ ] Base de données créée et schéma importé
- [ ] Fichier `.env` configuré
- [ ] Dépendances Composer installées
- [ ] Permissions correctes configurées
- [ ] CRON jobs configurés
- [ ] SSL/HTTPS activé
- [ ] Firewall configuré
- [ ] Compte Super Admin créé
- [ ] Services externes configurés (Twilio, Stripe, SMTP)
- [ ] Tests de connexion réussis
- [ ] Sauvegardes automatiques actives
- [ ] Monitoring configuré

---

## 🚀 C'est Parti !

Votre application **Pakiparc Fleet Management v3.0.0** est maintenant **EN LIGNE** ! 🎉

**Prochaines étapes**:

1. ✅ Connexion avec le compte Super Admin
2. ✅ Créer votre première entreprise
3. ✅ Ajouter des utilisateurs
4. ✅ Importer vos véhicules et conducteurs
5. ✅ Commencer à suivre votre flotte !

**Profitez de toutes les fonctionnalités Enterprise** :
- 📊 Analytics avancés
- 🚨 Alertes intelligentes
- 📧 Notifications multi-canal
- 🌱 Suivi carbone
- 🛡️ Conformité RGPD
- 💾 Sauvegardes automatiques
- 🔌 API REST complète
- 🎨 Mode sombre
- 💳 Paiements Stripe
- 🌍 5 langues supportées

---

**Made with ❤️ by Pakiparc Team**

*Version 3.0.0 Enterprise Edition | 2025*
