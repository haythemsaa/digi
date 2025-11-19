# Pakiparc Driver - Application Mobile Chauffeur

Application mobile Progressive Web App (PWA) pour les chauffeurs du système Pakiparc Fleet Management.

## 🚀 Fonctionnalités

### Authentification
- Connexion sécurisée avec identifiant et mot de passe
- Option "Se souvenir de moi"
- Déconnexion sécurisée

### Tableau de Bord
- Statistiques en temps réel:
  - Missions actives
  - Livraisons du jour
  - KM parcourus
  - Heures de travail
- Liste des missions du jour
- Actions rapides (Démarrer mission, Urgence)

### Gestion des Missions
- Liste des missions assignées
- Filtrage par statut (En cours / Terminées)
- Détails complets de chaque mission
- Acceptation/Refus de missions

### Suivi en Temps Réel
- Carte GPS interactive avec Leaflet.js
- Localisation en temps réel du chauffeur
- Itinéraire de la mission en cours
- Étapes de livraison:
  - Arrivé au point de départ
  - Chargement effectué
  - Arrivé à destination
  - Livraison effectuée

### Profil Chauffeur
- Informations personnelles
- Statistiques de performance:
  - Missions totales
  - KM total
  - Évaluation moyenne
- Historique des missions
- Paramètres

### Mode Hors Ligne
- Fonctionnement offline complet
- Synchronisation automatique des données
- Mise en cache des ressources essentielles
- File d'attente pour les actions offline

### Notifications
- Notifications push pour nouvelles missions
- Alertes en temps réel
- Notifications de rappel

## 📱 Technologies Utilisées

- **HTML5** - Structure de l'application
- **CSS3** - Design moderne et responsive
- **JavaScript (ES6+)** - Logique métier
- **Service Worker** - Fonctionnalités offline et cache
- **Geolocation API** - Localisation GPS
- **Leaflet.js** - Cartes interactives
- **Font Awesome** - Icônes
- **LocalStorage** - Stockage local des données
- **Fetch API** - Communication avec le backend

## 🛠️ Installation

### Prérequis
- Serveur web (Apache/Nginx)
- HTTPS (requis pour les Service Workers)
- Backend Pakiparc API configuré

### Étapes d'Installation

1. **Copier les fichiers**
   ```bash
   cp -r mobile-driver-app /var/www/html/
   ```

2. **Configuration**
   Éditer `js/config.js` pour configurer l'URL de l'API:
   ```javascript
   API_BASE_URL: 'https://votre-domaine.com/api'
   ```

3. **Permissions HTTPS**
   L'application nécessite HTTPS pour:
   - Service Workers
   - Geolocation
   - Notifications Push

4. **Accès**
   Ouvrir dans le navigateur:
   ```
   https://votre-domaine.com/mobile-driver-app/
   ```

## 📲 Installation sur Mobile

### Android
1. Ouvrir l'application dans Chrome
2. Menu > "Ajouter à l'écran d'accueil"
3. L'application s'installera comme une app native

### iOS
1. Ouvrir l'application dans Safari
2. Partager > "Sur l'écran d'accueil"
3. L'application s'ajoutera à l'écran d'accueil

## 🔧 Configuration

### Fichiers de Configuration

#### `js/config.js`
```javascript
const CONFIG = {
    API_BASE_URL: 'https://api.pakiparc.com',
    LOCATION_UPDATE_INTERVAL: 30000, // 30 secondes
    SYNC_INTERVAL: 60000, // 1 minute
    // ...
};
```

### Intervalles de Mise à Jour
- **Localisation**: 30 secondes (modifiable)
- **Synchronisation**: 1 minute (modifiable)
- **Notifications**: Temps réel (Push)

## 🗺️ Fonctionnalités GPS

### Permissions Requises
L'application demande l'autorisation d'accès à:
- Localisation GPS en temps réel
- Localisation en arrière-plan

### Suivi de Position
- Précision haute activée
- Mise à jour continue pendant les missions
- Envoi automatique au serveur
- Stockage local en cas de perte de connexion

### Calcul de Distance
- Distance entre deux points géographiques
- Détection de proximité (géofencing)
- Calcul d'itinéraire

## 🔔 Notifications

### Types de Notifications
1. **Nouvelles Missions** - Assignation de nouvelle mission
2. **Rappels** - Rappels pour actions en attente
3. **Urgences** - Alertes importantes
4. **Système** - Mises à jour et synchronisation

### Configuration
Les notifications peuvent être activées/désactivées dans:
- Paramètres de l'application
- Paramètres du navigateur/système

## 💾 Stockage des Données

### LocalStorage
- Token d'authentification
- Données utilisateur
- Préférences
- Cache des missions

### Cache du Service Worker
- Ressources statiques (HTML, CSS, JS)
- Images et icônes
- Cartes hors ligne
- Réponses API

### Synchronisation
- Automatique toutes les minutes
- Manuelle via bouton de synchronisation
- En arrière-plan via Background Sync API

## 🔐 Sécurité

### Authentification
- Token JWT stocké sécurisé
- Expiration automatique du token
- Déconnexion automatique après inactivité

### Communication
- HTTPS obligatoire
- Chiffrement des données
- Validation des entrées utilisateur

### Permissions
- Géolocalisation avec consentement
- Notifications avec consentement
- Accès limité aux données nécessaires

## 📊 API Endpoints Utilisés

```
POST   /api/login                      - Connexion
POST   /api/logout                     - Déconnexion
GET    /api/driver/profile             - Profil chauffeur
GET    /api/driver/orders              - Liste des missions
GET    /api/driver/orders/:id          - Détail mission
PUT    /api/driver/orders/:id/status   - Mise à jour statut
GET    /api/driver/current-trip        - Mission en cours
POST   /api/driver/location            - Mise à jour position
GET    /api/driver/stats               - Statistiques
GET    /api/driver/notifications       - Notifications
```

## 🐛 Débogage

### Console de Développement
```javascript
// Activer les logs détaillés
localStorage.setItem('debug', 'true');

// Vider le cache
navigator.serviceWorker.getRegistrations().then(registrations => {
    registrations.forEach(registration => registration.unregister());
});
```

### Tester le Mode Offline
1. Ouvrir DevTools (F12)
2. Onglet "Network"
3. Activer "Offline"
4. Tester les fonctionnalités

## 📱 Support des Navigateurs

### Navigateurs Supportés
- ✅ Chrome 80+ (Android/Desktop)
- ✅ Safari 13+ (iOS/macOS)
- ✅ Firefox 75+ (Android/Desktop)
- ✅ Edge 80+ (Desktop)
- ✅ Samsung Internet 12+

### Fonctionnalités Requises
- Service Workers
- Geolocation API
- LocalStorage
- Fetch API
- Promises

## 🔄 Mises à Jour

### Version Actuelle: 1.0.0

### Historique des Versions
- **1.0.0** (2024) - Version initiale
  - Authentification
  - Gestion des missions
  - Suivi GPS
  - Mode offline
  - Notifications

### Processus de Mise à Jour
1. Modifier `CACHE_NAME` dans `service-worker.js`
2. Déployer les nouveaux fichiers
3. L'application se mettra à jour automatiquement

## 📞 Support

Pour toute question ou problème:
- **Email**: support@pakiparc.com
- **Documentation**: https://docs.pakiparc.com
- **Issues**: https://github.com/pakiparc/driver-app/issues

## 📄 Licence

© 2024 Pakiparc Fleet Management. Tous droits réservés.

## 🎯 Roadmap Future

- [ ] Chat en temps réel avec le dispatcher
- [ ] Scan de code-barres pour colis
- [ ] Signature électronique de livraison
- [ ] Photos de preuve de livraison
- [ ] Rapports de frais
- [ ] Navigation intégrée (Waze/Google Maps)
- [ ] Mode nuit automatique
- [ ] Multi-langue (FR/EN/AR)
- [ ] Optimisation de la batterie
- [ ] Statistiques détaillées de conduite

## 🏗️ Architecture

```
mobile-driver-app/
├── index.html              # Page principale
├── manifest.json           # PWA manifest
├── service-worker.js       # Service Worker
├── css/
│   └── app.css            # Styles
├── js/
│   ├── config.js          # Configuration
│   ├── api.js             # Client API
│   ├── storage.js         # Gestion du stockage
│   ├── location.js        # Géolocalisation
│   └── app.js             # Logique principale
└── img/
    └── icons/             # Icônes PWA
```

## 👨‍💻 Développement

### Environnement de Développement
```bash
# Lancer un serveur local HTTPS
npx http-server -S -C cert.pem -K key.pem

# Ou avec Python
python -m http.server 8000
```

### Tests
```bash
# Test de fonctionnalité offline
# 1. Charger l'application
# 2. Activer le mode offline dans DevTools
# 3. Tester la navigation et les fonctionnalités

# Test de performance
# Lighthouse audit dans Chrome DevTools
```

---

**Made with ❤️ for Pakiparc Drivers**
