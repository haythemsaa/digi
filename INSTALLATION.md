# Guide d'Installation Complet - DigiParc Fleet Management

## 📋 Table des matières
1. [Prérequis](#prérequis)
2. [Installation sur Windows (XAMPP)](#installation-windows)
3. [Installation sur Linux (Ubuntu/Debian)](#installation-linux)
4. [Installation sur macOS](#installation-macos)
5. [Configuration](#configuration)
6. [Vérification](#vérification)
7. [Dépannage](#dépannage)

---

## Prérequis

### Logiciels requis
- **PHP** >= 7.4
- **MySQL** >= 5.7 ou **MariaDB** >= 10.2
- **Apache** >= 2.4 avec mod_rewrite
- **Navigateur moderne** (Chrome, Firefox, Edge, Safari)

### Extensions PHP requises
- pdo
- pdo_mysql
- mbstring
- json
- fileinfo
- curl
- openssl

---

## Installation Windows (XAMPP)

### 1. Installer XAMPP

1. Télécharger XAMPP depuis https://www.apachefriends.org/
2. Installer XAMPP (recommandé: `C:\xampp`)
3. Démarrer Apache et MySQL depuis le Control Panel XAMPP

### 2. Copier les fichiers

```bash
# Copier le dossier 'digi' dans htdocs
C:\xampp\htdocs\digi
```

### 3. Créer la base de données

1. Ouvrir phpMyAdmin: http://localhost/phpmyadmin
2. Créer une nouvelle base de données:
   - Nom: `digiparc_fleet`
   - Collation: `utf8mb4_unicode_ci`
3. Sélectionner la base de données
4. Cliquer sur "Importer"
5. Choisir le fichier `database/schema.sql`
6. Cliquer sur "Exécuter"

### 4. Configuration

Éditer `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'digiparc_fleet');
define('DB_USER', 'root');
define('DB_PASS', ''); // Vide par défaut sur XAMPP
```

Éditer `config/config.php`:
```php
define('APP_URL', 'http://localhost/digi');
define('APP_ENV', 'development');
```

### 5. Créer les dossiers uploads

```bash
mkdir C:\xampp\htdocs\digi\public\uploads
mkdir C:\xampp\htdocs\digi\public\uploads\vehicles
mkdir C:\xampp\htdocs\digi\public\uploads\documents
```

### 6. Accéder à l'application

Ouvrir dans le navigateur: http://localhost/digi

**Identifiants par défaut:**
- Email: `admin@digiparc.local`
- Mot de passe: `admin123`

---

## Installation Linux (Ubuntu/Debian)

### 1. Installer les prérequis

```bash
# Mettre à jour le système
sudo apt update
sudo apt upgrade -y

# Installer Apache
sudo apt install apache2 -y

# Installer MySQL
sudo apt install mysql-server -y

# Installer PHP et extensions
sudo apt install php php-mysql php-mbstring php-json php-curl php-gd -y

# Activer mod_rewrite
sudo a2enmod rewrite

# Redémarrer Apache
sudo systemctl restart apache2
```

### 2. Cloner ou copier les fichiers

```bash
# Aller dans le répertoire web
cd /var/www/html

# Cloner le projet (ou copier les fichiers)
sudo git clone https://github.com/votre-compte/digi.git
# OU
sudo cp -r /chemin/vers/digi .

# Permissions
sudo chown -R www-data:www-data digi
sudo chmod -R 755 digi
```

### 3. Configurer MySQL

```bash
# Se connecter à MySQL
sudo mysql -u root

# Dans MySQL:
CREATE DATABASE digiparc_fleet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'digiparc_user'@'localhost' IDENTIFIED BY 'VotreMotDePasse123!';
GRANT ALL PRIVILEGES ON digiparc_fleet.* TO 'digiparc_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importer le schéma
sudo mysql -u digiparc_user -p digiparc_fleet < /var/www/html/digi/database/schema.sql
```

### 4. Configuration Apache Virtual Host

```bash
# Créer le fichier de configuration
sudo nano /etc/apache2/sites-available/digiparc.conf
```

Contenu du fichier:
```apache
<VirtualHost *:80>
    ServerName digiparc.local
    ServerAlias www.digiparc.local
    DocumentRoot /var/www/html/digi

    <Directory /var/www/html/digi>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/digiparc-error.log
    CustomLog ${APACHE_LOG_DIR}/digiparc-access.log combined
</VirtualHost>
```

```bash
# Activer le site
sudo a2ensite digiparc.conf

# Désactiver le site par défaut (optionnel)
sudo a2dissite 000-default.conf

# Redémarrer Apache
sudo systemctl restart apache2

# Ajouter au fichier hosts
sudo nano /etc/hosts
# Ajouter: 127.0.0.1    digiparc.local
```

### 5. Configuration de l'application

```bash
# Éditer la configuration de la base de données
sudo nano /var/www/html/digi/config/database.php
```

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'digiparc_fleet');
define('DB_USER', 'digiparc_user');
define('DB_PASS', 'VotreMotDePasse123!');
```

```bash
# Éditer la configuration générale
sudo nano /var/www/html/digi/config/config.php
```

```php
define('APP_URL', 'http://digiparc.local');
define('APP_ENV', 'production');
define('ENCRYPTION_KEY', 'générer-une-clé-aléatoire-sécurisée');
```

### 6. Créer les dossiers uploads

```bash
sudo mkdir -p /var/www/html/digi/public/uploads/{vehicles,documents,drivers,parts}
sudo chown -R www-data:www-data /var/www/html/digi/public/uploads
sudo chmod -R 775 /var/www/html/digi/public/uploads
```

### 7. Accéder à l'application

Ouvrir dans le navigateur: http://digiparc.local

---

## Installation macOS

### 1. Installer Homebrew (si pas déjà installé)

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### 2. Installer les prérequis

```bash
# Installer PHP
brew install php@8.1

# Installer MySQL
brew install mysql

# Démarrer MySQL
brew services start mysql

# Installer Apache (ou utiliser celui par défaut de macOS)
brew install httpd
brew services start httpd
```

### 3. Configurer MySQL

```bash
# Sécuriser MySQL
mysql_secure_installation

# Se connecter
mysql -u root -p

# Créer la base de données
CREATE DATABASE digiparc_fleet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'digiparc_user'@'localhost' IDENTIFIED BY 'VotreMotDePasse123!';
GRANT ALL PRIVILEGES ON digiparc_fleet.* TO 'digiparc_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Configurer les fichiers

```bash
# Copier les fichiers dans le répertoire web
sudo cp -R digi /usr/local/var/www/

# Importer le schéma
mysql -u digiparc_user -p digiparc_fleet < /usr/local/var/www/digi/database/schema.sql

# Permissions
sudo chown -R _www:_www /usr/local/var/www/digi
sudo chmod -R 755 /usr/local/var/www/digi
```

### 5. Configuration

Même configuration que Linux pour les fichiers `config/database.php` et `config/config.php`

### 6. Accéder à l'application

http://localhost:8080/digi

---

## Configuration

### Sécurité

1. **Changer le mot de passe admin** immédiatement après la première connexion

2. **Générer une clé de cryptage unique** dans `config/config.php`:
```bash
# Générer une clé aléatoire
php -r "echo bin2hex(random_bytes(32));"
```

3. **Configurer HTTPS** pour la production:
```bash
# Installer Certbot (Let's Encrypt)
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d digiparc.votre-domaine.com
```

### Email (SMTP)

Éditer `config/config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre-email@gmail.com');
define('SMTP_PASS', 'votre-mot-de-passe-application');
```

### API GPS

Pour utiliser Google Maps, obtenir une clé API:
```php
define('MAPS_API_KEY', 'votre-clé-google-maps');
```

---

## Vérification

### Tests de base

1. **Page de login** : http://localhost/digi
2. **Connexion admin** : admin@digiparc.local / admin123
3. **Dashboard** : Devrait afficher les statistiques
4. **Ajout de véhicule** : Tester l'ajout d'un véhicule
5. **Upload de fichiers** : Vérifier que les uploads fonctionnent

### Vérifier les logs

```bash
# Apache error log
tail -f /var/log/apache2/error.log

# MySQL log
sudo tail -f /var/log/mysql/error.log

# PHP errors (si mode développement)
# Affichés directement dans le navigateur
```

---

## Dépannage

### Erreur "Database connection failed"

```bash
# Vérifier que MySQL est démarré
sudo systemctl status mysql

# Tester la connexion
mysql -u digiparc_user -p digiparc_fleet

# Vérifier les credentials dans config/database.php
```

### Erreur 404 / .htaccess ne fonctionne pas

```bash
# Vérifier mod_rewrite
sudo a2enmod rewrite

# Vérifier AllowOverride dans Apache config
sudo nano /etc/apache2/apache2.conf
# Chercher <Directory /var/www/> et mettre AllowOverride All

# Redémarrer Apache
sudo systemctl restart apache2
```

### Problèmes de permissions

```bash
# Linux
sudo chown -R www-data:www-data /var/www/html/digi
sudo chmod -R 755 /var/www/html/digi
sudo chmod -R 775 /var/www/html/digi/public/uploads

# macOS
sudo chown -R _www:_www /usr/local/var/www/digi
sudo chmod -R 755 /usr/local/var/www/digi
```

### Page blanche

1. Activer l'affichage des erreurs dans `config/config.php`:
```php
define('APP_ENV', 'development');
```

2. Vérifier les logs Apache/PHP

3. Vérifier que toutes les extensions PHP sont installées:
```bash
php -m | grep -E 'pdo|mysql|mbstring|json'
```

---

## Support

Pour plus d'aide:
- 📧 Email: support@digiparc.local
- 📚 Documentation complète: README.md
- 🐛 Rapporter un bug: GitHub Issues

---

**Bonne installation! 🚀**
