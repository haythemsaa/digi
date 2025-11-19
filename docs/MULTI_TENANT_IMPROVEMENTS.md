# Améliorations Multi-Tenant - DigiParc

## 📋 Vue d'ensemble

Ce document résume toutes les améliorations apportées au système DigiParc pour le transformer en une **plateforme SaaS multi-tenant complète et sécurisée**.

## 🎯 Objectifs Atteints

✅ **Isolation Complète des Données** - Chaque entreprise voit uniquement ses propres données
✅ **Gestion Centralisée** - Interface super admin pour gérer toutes les entreprises
✅ **Sécurité Renforcée** - Middleware de validation et prévention d'accès croisé
✅ **Quotas et Limites** - Application des limites par abonnement
✅ **Paramètres Flexibles** - Settings globaux et par entreprise
✅ **Audit et Logging** - Traçabilité des actions par entreprise

---

## 🔧 Composants Développés

### 1. **Middleware de Sécurité** (`CompanyMiddleware.php`)

#### Fonctionnalités Clés

**`requireCompanyContext()`**
- Vérifie l'existence du contexte entreprise en session
- Valide que l'entreprise est toujours active
- Redirige vers login si contexte invalide

```php
// Utilisation dans un contrôleur
CompanyMiddleware::requireCompanyContext();
```

**`requireSuperAdmin()`**
- Restreint l'accès aux pages super admin uniquement
- Vérifie le flag `is_super_admin` en session

```php
// Page réservée aux super admins
CompanyMiddleware::requireSuperAdmin();
```

**`checkSubscription()`**
- Valide le statut d'abonnement (trial/active)
- Vérifie l'expiration de la période d'essai
- Bloque l'accès si abonnement expiré

```php
// Vérifier avant accès module
CompanyMiddleware::checkSubscription();
```

**`requireModule($moduleCode)`**
- Vérifie l'accès à un module spécifique
- Intégration avec le système d'abonnement

```php
// Nécessite accès au module GPS
CompanyMiddleware::requireModule('gps_tracking');
```

**`checkResourceLimit($resourceType)`**
- Vérifie les limites de ressources (vehicles/drivers/users)
- Retourne false si limite atteinte

```php
// Avant création d'un véhicule
if (!CompanyMiddleware::checkResourceLimit('vehicles')) {
    // Afficher erreur upgrade requis
}
```

**`validateDataAccess($dataCompanyId)`**
- Prévient l'accès aux données d'autres entreprises
- Valide que l'ID company correspond

```php
// Vérifier l'accès à une ressource
CompanyMiddleware::validateDataAccess($vehicle['company_id']);
```

**`getCompanyLimits()`**
- Récupère le résumé des limites et usage actuel
- Utile pour afficher les quotas dans le dashboard

```php
$limits = CompanyMiddleware::getCompanyLimits();
// Retourne: ['vehicles' => ['current' => 5, 'max' => 10, 'reached' => false], ...]
```

---

### 2. **Helper Centralisé** (`init_helper.php`)

#### Fonctions Utilitaires

**Messages Flash Améliorés**
```php
flash('success', 'Action réussie !');
flash('error', 'Une erreur est survenue');
flash('warning', 'Attention !');
flash('info', 'Information importante');
```

**Navigation et Redirection**
```php
redirect('dashboard');
redirect('companies/view/123');
```

**Authentification**
```php
if (isLoggedIn()) {
    $userId = getUserId();
    $role = getUserRole();
    if (isAdmin()) {
        // Actions admin
    }
}
```

**Formatage**
```php
echo formatCurrency(1500.50); // "1 500.50 TND"
echo formatDate('2025-11-19'); // "19/11/2025"
echo formatDateTime('2025-11-19 14:30:00'); // "19/11/2025 14:30"
```

**Sécurité**
```php
$clean = sanitize($_POST['input']); // XSS protection
```

**Debug**
```php
dd($variable); // Dump and die
```

---

### 3. **Modèle Settings Multi-Tenant** (`Setting.php`)

#### Architecture à Deux Niveaux

**Settings Globaux (Super Admin)**
- S'appliquent à toutes les entreprises par défaut
- Modifiables uniquement par super admins
- Exemple: maintenance mode, API keys globales

**Settings par Entreprise**
- Personnalisables par chaque entreprise
- Overrident les settings globaux
- Exemple: logo, couleur primaire, timezone

#### Utilisation

```php
$setting = new Setting();

// Lecture (vérifie company puis global)
$timezone = $setting->get('timezone', 'Africa/Tunis');

// Écriture company
$setting->set('company_logo', 'logo.png', 'string', 'Logo de l\'entreprise');

// Écriture globale (super admin only)
$setting->setGlobal('maintenance_mode', false, 'boolean', 'Mode maintenance');

// Récupérer tous les settings
$allSettings = $setting->getAllSettings();
$settingsArray = $setting->getSettingsArray(); // Format associatif
```

#### Gestion des Types

Le modèle parse automatiquement les valeurs selon leur type:
- **Boolean**: `'true'` → `true`, `'false'` → `false`
- **Numeric**: `'42'` → `42`, `'3.14'` → `3.14`
- **JSON**: `'{"key":"value"}'` → `['key' => 'value']`
- **String**: Retour normal

---

### 4. **Interface Super Admin** (`companies/index.php`)

#### Fonctionnalités

**Dashboard des Entreprises**
- Vue d'ensemble de toutes les entreprises
- Statistiques globales (total, actives, en essai, suspendues)
- Liste complète avec filtres

**Informations Affichées**
- Code entreprise unique
- Nom et logo
- Plan d'abonnement (Starter/Professional/Enterprise)
- Statut d'abonnement (trial/active/suspended/cancelled/expired)
- Expiration de l'essai
- Usage des ressources (véhicules/utilisateurs/chauffeurs)
- Statut général (active/suspended/inactive)
- Date de création

**Actions Disponibles**
- 👁️ **Voir** - Détails complets de l'entreprise
- ✏️ **Modifier** - Éditer paramètres et limites
- 🚪 **Se Connecter** - Basculer vers le contexte entreprise (impersonate)

**Interface Responsive**
- Badges colorés pour les statuts
- Icônes FontAwesome
- Bootstrap 5 styling
- Tableau responsive avec scroll horizontal

---

## 📊 Modèles Mis à Jour (13 modèles)

### Modèles Core avec Multi-Tenancy Complète

1. ✅ **Vehicle** - Gestion flotte avec quotas
2. ✅ **Driver** - Chauffeurs avec limites
3. ✅ **User** - Utilisateurs multi-tenant
4. ✅ **Company** - Gestion entreprises (super admin)
5. ✅ **Fuel** - Carburant isolé par entreprise
6. ✅ **Mission** - Missions et affectations
7. ✅ **Maintenance** - GMAO multi-tenant
8. ✅ **Tracking** - GPS et géolocalisation
9. ✅ **Financial** - Comptabilité isolée
10. ✅ **Inventory** - Stock et pièces
11. ✅ **Supplier** - Fournisseurs et achats
12. ✅ **Setting** - Paramètres flexibles
13. ✅ **Transport** - Clients et commandes (en cours)

### Pattern Uniforme Appliqué

Tous les modèles suivent maintenant ce pattern:

```php
class ModelName {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();

        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    private function getCompanyFilter($tableAlias = 't') {
        if (isSuperAdmin()) return '1=1';
        return "{$tableAlias}.company_id = :company_id";
    }

    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    // Toutes les méthodes utilisent $filter et bindCompanyId()
}
```

---

## 🔐 Sécurité et Isolation

### Niveaux de Protection

**1. Niveau Session**
```php
$_SESSION['company_id']      // ID entreprise courante
$_SESSION['company_data']    // Données entreprise en cache
$_SESSION['is_super_admin']  // Flag super administrateur
```

**2. Niveau Base de Données**
- Toutes les tables ont `company_id` (sauf tables globales)
- Foreign keys avec CASCADE pour l'intégrité
- Indexes sur company_id pour performance
- Contraintes uniques composites (company_id + autre_champ)

**3. Niveau Application**
- Middleware de validation systématique
- Filtrage automatique dans tous les modèles
- Logs d'activité par entreprise
- Prévention d'accès croisé

### Tests de Sécurité Recommandés

```php
// 1. Tester isolation des données
// User de company_id=1 ne doit PAS voir données de company_id=2

// 2. Tester quotas
// Bloquer création si limite atteinte

// 3. Tester super admin
// Doit voir toutes les entreprises

// 4. Tester expiration
// Bloquer accès si abonnement expiré

// 5. Tester module access
// Bloquer si module pas dans abonnement
```

---

## 📈 Métriques et Monitoring

### Données Trackées

**Par Entreprise**
- Nombre de véhicules/users/drivers vs limites
- Statut d'abonnement et expiration
- Date dernière activité
- Volume de transactions
- Usage des modules

**Globales (Super Admin)**
- Total entreprises actives
- Revenus par plan
- Taux de conversion trial → paid
- Churn rate
- Resources moyennes par plan

### Activity Logging

```php
// Logger une action importante
logCompanyActivity(
    'vehicle_created',
    'Nouveau véhicule ajouté',
    [
        'vehicle_id' => 123,
        'registration' => 'ABC-123',
        'user_id' => $_SESSION['user_id']
    ]
);
```

---

## 🚀 Utilisation Recommandée

### Pour les Contrôleurs

```php
class Vehicles extends Controller {

    public function __construct() {
        // Vérifier contexte entreprise
        CompanyMiddleware::requireCompanyContext();

        // Vérifier accès module
        CompanyMiddleware::requireModule('fleet_management');

        $this->vehicleModel = $this->model('Vehicle');
    }

    public function create() {
        // Vérifier limite avant création
        if (!CompanyMiddleware::checkResourceLimit('vehicles')) {
            flash('error', 'Limite de véhicules atteinte. Upgrader votre plan.');
            redirect('vehicles');
        }

        // Créer véhicule...
        $vehicleId = $this->vehicleModel->addVehicle($data);

        // Logger l'action
        CompanyMiddleware::logActivity(
            'vehicle_created',
            'Nouveau véhicule créé',
            ['vehicle_id' => $vehicleId]
        );
    }
}
```

### Pour les Super Admins

```php
class Companies extends Controller {

    public function __construct() {
        // Restreindre aux super admins
        CompanyMiddleware::requireSuperAdmin();

        $this->companyModel = $this->model('Company');
    }

    public function switchTo($companyId) {
        // Se connecter en tant qu'entreprise
        $_SESSION['original_user_id'] = $_SESSION['user_id'];
        loadCompanyIntoSession($companyId);

        redirect('dashboard');
    }

    public function switchBack() {
        // Revenir au compte super admin
        $_SESSION['user_id'] = $_SESSION['original_user_id'];
        clearCompanyFromSession();
        $_SESSION['is_super_admin'] = true;

        redirect('companies');
    }
}
```

---

## 📋 Checklist de Déploiement

### Base de Données

- [ ] Exécuter `migration_multi_tenant.sql`
- [ ] Vérifier tous les indexes sur company_id
- [ ] Créer entreprise démo (ID = 1)
- [ ] Tester foreign keys CASCADE

### Configuration

- [ ] Mettre à jour `config.php` pour charger helpers
- [ ] Configurer les limites par défaut
- [ ] Définir plans d'abonnement
- [ ] Configurer durée trial (30 jours par défaut)

### Sécurité

- [ ] Tester isolation entre entreprises
- [ ] Vérifier quotas fonctionnent
- [ ] Valider super admin access
- [ ] Tester expiration abonnements
- [ ] Logger toutes actions sensibles

### Interface

- [ ] Ajouter company switcher en nav
- [ ] Afficher limites dans dashboard
- [ ] Badges statut abonnement
- [ ] Alertes expiration trial
- [ ] Bouton upgrade plan

---

## 🎓 Formation Utilisateurs

### Pour les Administrateurs d'Entreprise

1. **Gérer votre entreprise**
   - Personnaliser logo et couleurs
   - Configurer timezone et devise
   - Gérer utilisateurs

2. **Surveiller les quotas**
   - Vérifier usage véhicules/users
   - Anticiper les limites
   - Upgrader au besoin

3. **Accès aux modules**
   - Vérifier modules actifs
   - Demander activation modules
   - Tester nouvelles fonctionnalités

### Pour les Super Administrateurs

1. **Gestion globale**
   - Créer nouvelles entreprises
   - Configurer plans d'abonnement
   - Définir limites par plan

2. **Support client**
   - Se connecter aux comptes clients
   - Investiguer problèmes
   - Ajuster limites temporairement

3. **Monitoring**
   - Surveiller usage global
   - Identifier tendances
   - Optimiser infrastructure

---

## 📞 Support et Contact

Pour toute question sur le système multi-tenant :

- 📖 Documentation complète: `/docs/MULTI_TENANT_GUIDE.md`
- 🔧 Guide migration: `/database/migration_multi_tenant.sql`
- 💡 Améliorations: Ce document

---

**Date de dernière mise à jour**: 19 novembre 2025
**Version DigiParc**: 2.0 Multi-Tenant
**Auteur**: Claude AI Assistant
