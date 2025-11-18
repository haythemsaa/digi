# DigiParc - Fleet Management System

Une application complète de gestion de flotte professionnelle développée en PHP, Bootstrap 5 et JavaScript. Clone complet du système DigiParc avec toutes les fonctionnalités professionnelles.

## 🚀 Fonctionnalités

### 📊 Module de Gestion de Flotte
- **Gestion complète des véhicules (CRUD)**
  - Enregistrement complet des véhicules avec toutes les spécifications
  - Photos des véhicules
  - Suivi du kilométrage
  - Documents numériques (assurance, carte grise, contrôle technique)
  - Alertes d'expiration des documents

- **Affectation des véhicules**
  - Assignment aux conducteurs
  - Historique des affectations
  - Gestion des réservations

- **Gestion du carburant**
  - Enregistrement des pleins
  - Calcul de consommation
  - Coûts et statistiques
  - Reçus numérisés

### 🗺️ Module GPS & Télématique
- **Suivi en temps réel**
  - Localisation GPS en direct
  - Cartes interactives (Leaflet/OpenStreetMap)
  - Historique des trajets
  - Tableau de bord de suivi

- **Géofencing (Zones géographiques)**
  - Création de zones circulaires ou polygonales
  - Alertes d'entrée/sortie de zone
  - Visualisation sur carte

- **Alertes de vitesse**
  - Détection de dépassement de vitesse
  - Notifications en temps réel
  - Historique des infractions

- **Analyse de conduite**
  - Comportement du conducteur
  - Statistiques de vitesse
  - Temps d'arrêt et ralenti
  - Consommation de carburant par trajet

### 🚚 Module de Transport (TMS)
- **Gestion des clients**
  - Base de données clients (particuliers/entreprises)
  - Historique des commandes
  - Conditions de paiement

- **Devis de transport**
  - Création et envoi de devis
  - Calcul automatique des prix
  - Conversion en commande

- **Ordres de transport**
  - Gestion complète des commandes
  - Affectation véhicule/conducteur
  - Suivi de statut
  - Preuve de livraison
  - Signature électronique

- **Facturation**
  - Génération automatique des factures
  - Suivi des paiements
  - Rapports de rentabilité

### 🔧 Module de Maintenance (GMAO)
- **Maintenance préventive**
  - Planification automatique
  - Alertes basées sur le kilométrage ou le temps
  - Types de maintenance personnalisables

- **Ordres de travail**
  - Création et assignation
  - Suivi de statut
  - Coûts (main d'œuvre + pièces)
  - Historique complet

- **Gestion des pneus**
  - Position et suivi
  - Rotation et remplacement
  - Pression et profondeur

### 👥 Module RH & Conducteurs
- **Profils des conducteurs**
  - Informations complètes
  - Permis de conduire et validité
  - Visites médicales
  - Contrats et salaires

- **Infractions**
  - Enregistrement des infractions
  - Amendes et points
  - Suivi des paiements

- **Formations**
  - Planification des formations
  - Certificats
  - Historique

### 💰 Module Financier
- **Gestion des comptes**
  - Comptes bancaires multiples
  - Cartes de crédit
  - Caisse

- **Transactions**
  - Revenus et dépenses
  - Catégorisation
  - Rapprochement bancaire

- **Rapports financiers**
  - Tableau de bord financier
  - Analyse des coûts
  - Rentabilité par véhicule

### 📦 Module d'Approvisionnement
- **Gestion des fournisseurs**
  - Base de données fournisseurs
  - Catégorisation
  - Conditions de paiement

- **Bons de commande**
  - Création et envoi
  - Suivi de réception
  - Historique des achats

### 🏪 Module Inventaire
- **Gestion des pièces**
  - Catalogue complet
  - Stock minimum
  - Localisation dans l'entrepôt
  - Photos des pièces

- **Mouvements de stock**
  - Entrées/Sorties
  - Ajustements
  - Traçabilité complète

### 📱 Interface Mobile
- **Design responsive**
  - Bootstrap 5 adaptatif
  - PWA (Progressive Web App)
  - Compatible tous appareils

- **API REST**
  - Endpoints pour mobile
  - Authentification sécurisée
  - Format JSON

### 📊 Rapports & Analytics
- **Tableaux de bord**
  - Statistiques en temps réel
  - Graphiques interactifs (Chart.js)
  - KPIs personnalisables

- **Exports**
  - PDF
  - Excel
  - Impression directe

### 🔔 Système de Notifications
- **Alertes multiples**
  - Email (SMTP)
  - SMS (API)
  - Notifications in-app

- **Types d'alertes**
  - Maintenance due
  - Documents expirés
  - Alertes GPS
  - Factures impayées

### 🔐 Sécurité & Permissions
- **Authentification robuste**
  - Hashing bcrypt
  - Sessions sécurisées
  - Protection CSRF

- **Système de rôles**
  - Admin (accès complet)
  - Manager (gestion opérationnelle)
  - Dispatcher (transport et GPS)
  - Driver (consultation)
  - Mechanic (maintenance)
  - Accountant (financier)

- **Audit Log**
  - Traçabilité complète des actions
  - Historique des modifications

## 🛠️ Technologies Utilisées

### Backend
- **PHP 7.4+** - Langage serveur
- **MySQL/MariaDB** - Base de données
- **PDO** - Abstraction base de données
- **Architecture MVC** - Organisation du code

### Frontend
- **Bootstrap 5.3** - Framework CSS responsive
- **Font Awesome 6** - Icônes
- **Chart.js** - Graphiques
- **DataTables** - Tables interactives
- **Leaflet.js** - Cartes GPS
- **SweetAlert2** - Alertes élégantes
- **jQuery** - Manipulation DOM

### Sécurité
- **Password hashing** (bcrypt)
- **Prepared statements** (SQL injection protection)
- **CSRF protection**
- **XSS prevention**
- **Input validation & sanitization**

## 📋 Prérequis

- **PHP** >= 7.4
- **MySQL** >= 5.7 ou **MariaDB** >= 10.2
- **Apache** avec mod_rewrite activé
- **Extensions PHP** requises:
  - PDO
  - PDO_MySQL
  - mbstring
  - json
  - fileinfo

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone https://github.com/votre-compte/digi.git
cd digi
```

### 2. Configuration de la base de données

Créer une base de données MySQL:
```sql
CREATE DATABASE digiparc_fleet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importer le schéma:
```bash
mysql -u root -p digiparc_fleet < database/schema.sql
```

### 3. Configuration de l'application

Éditer `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'digiparc_fleet');
define('DB_USER', 'root');
define('DB_PASS', 'votre_mot_de_passe');
```

Éditer `config/config.php`:
```php
define('APP_URL', 'http://localhost/digi');
define('APP_ENV', 'production'); // 'development' ou 'production'
define('ENCRYPTION_KEY', 'votre-clé-secrète-unique');
```

### 4. Permissions des dossiers

```bash
chmod -R 755 public/uploads
chmod -R 755 public/assets
```

### 5. Configuration Apache

Créer un Virtual Host ou copier le projet dans `htdocs/`:

**Virtual Host (.conf):**
```apache
<VirtualHost *:80>
    ServerName digiparc.local
    DocumentRoot /var/www/digi

    <Directory /var/www/digi>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/digiparc-error.log
    CustomLog ${APACHE_LOG_DIR}/digiparc-access.log combined
</VirtualHost>
```

Activer mod_rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 6. Connexion par défaut

- **URL**: http://localhost/digi
- **Email**: admin@digiparc.local
- **Password**: admin123

**⚠️ IMPORTANT**: Changez le mot de passe admin après la première connexion!

## 📱 API REST (Pour Mobile)

### Authentification
```
POST /api/login
Body: { "email": "...", "password": "..." }
Response: { "token": "...", "user": {...} }
```

### Récupérer les véhicules
```
GET /api/vehicles
Header: Authorization: Bearer {token}
Response: { "vehicles": [...] }
```

### Position GPS en temps réel
```
POST /api/gps/position
Header: Authorization: Bearer {token}
Body: { "vehicle_id": 1, "latitude": ..., "longitude": ..., "speed": ... }
```

## 🎨 Personnalisation

### Thème et couleurs
Modifiez `app/views/includes/header.php`:
```css
:root {
    --primary-color: #0d6efd;
    --secondary-color: #6c757d;
    /* ... */
}
```

### Logo
Remplacez le logo dans `public/assets/images/logo.png`

### Email templates
Modifiez les templates dans `app/views/emails/`

## 📊 Structure du Projet

```
digi/
├── app/
│   ├── controllers/      # Contrôleurs MVC
│   ├── models/           # Modèles de données
│   ├── views/            # Vues (HTML/PHP)
│   └── core/             # Classes de base (App, Controller, Database)
├── config/               # Configuration
├── database/             # Schéma SQL
├── public/               # Fichiers publics
│   ├── assets/           # CSS/JS personnalisés
│   └── uploads/          # Fichiers uploadés
├── vendor/               # Dépendances
├── .htaccess            # Configuration Apache
├── index.php            # Point d'entrée
├── composer.json        # Dépendances PHP
└── README.md            # Documentation
```

## 🔧 Développement

### Ajouter un nouveau module

1. Créer le modèle dans `app/models/`
2. Créer le contrôleur dans `app/controllers/`
3. Créer les vues dans `app/views/`
4. Ajouter l'entrée au menu dans `app/views/includes/sidebar.php`

### Base de données
Les migrations se trouvent dans `database/schema.sql`

### Debug
Activer le mode développement dans `config/config.php`:
```php
define('APP_ENV', 'development');
```

## 📈 Performance

### Optimisations recommandées

1. **Activer le cache PHP** (OPcache)
2. **Optimiser MySQL**:
   - Index sur les colonnes recherchées fréquemment
   - Query cache activé
3. **CDN** pour les assets statiques
4. **Compression Gzip**
5. **Minification** CSS/JS

## 🐛 Dépannage

### Erreur 500
- Vérifier les permissions des dossiers
- Vérifier les logs Apache: `/var/log/apache2/error.log`
- Activer le mode debug

### Problème de base de données
- Vérifier les credentials dans `config/database.php`
- Vérifier que MySQL est démarré
- Tester la connexion: `mysql -u root -p`

### .htaccess ne fonctionne pas
- Vérifier que mod_rewrite est activé
- Vérifier AllowOverride dans la config Apache

## 🤝 Contribution

Les contributions sont les bienvenues! Pour contribuer:

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changes (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👨‍💻 Auteur

Développé avec ❤️ pour la gestion professionnelle de flottes

## 🆘 Support

Pour le support:
- 📧 Email: support@digiparc.local
- 📚 Documentation: [Wiki](https://github.com/votre-compte/digi/wiki)
- 🐛 Issues: [GitHub Issues](https://github.com/votre-compte/digi/issues)

## 🎯 Roadmap

- [ ] Application mobile native (React Native)
- [ ] Intégration avec API de carburant
- [ ] Module de gestion de tournées optimisées
- [ ] Intelligence artificielle pour la maintenance prédictive
- [ ] Intégration avec télématique constructeurs
- [ ] Support multi-langue complet
- [ ] Mode hors-ligne pour l'application mobile

---

**Version actuelle**: 1.0.0
**Dernière mise à jour**: 2024
