# Guide Multi-Tenant DigiParc

## Vue d'ensemble

DigiParc est maintenant une plateforme SaaS multi-tenant complète. Chaque entreprise (company) dispose de son propre espace isolé avec ses propres données (véhicules, conducteurs, missions, transactions, etc.).

## Architecture

### Table Companies

La table centrale `companies` contient toutes les informations de l'entreprise:

```sql
- Informations de base: company_name, legal_name, tax_id
- Contact: email, phone, address, city, country
- Paramètres: timezone, currency, language, date_format
- Limites: max_users, max_vehicles, max_drivers
- Abonnement: subscription_status, trial_ends_at
- Branding: logo, primary_color, secondary_color
```

### Isolation des données

Toutes les tables principales incluent maintenant `company_id`:
- users
- vehicles
- drivers
- fuel_cards, fuel_transactions, fuel_alerts
- missions, mission_items, mission_billing, mission_updates
- Et toutes les autres tables métier

## Utilisation dans le code

### Helpers disponibles

```php
// Obtenir l'ID de l'entreprise courante
$companyId = getCurrentCompanyId();

// Obtenir les données de l'entreprise
$company = getCurrentCompany();

// Vérifier si super admin
if (isSuperAdmin()) {
    // Accès à toutes les données
}

// Exiger un contexte d'entreprise
requireCompany(); // Redirige si pas de company_id

// Obtenir un filtre SQL
$filter = companyFilter('v'); // v.company_id = :company_id

// Vérifier l'accès à une entreprise
if (canAccessCompany($someCompanyId)) {
    // Autorisé
}

// Paramètres de l'entreprise
$currency = getCompanySetting('currency', 'TND');
$timezone = getCompanyTimezone();

// Limites de ressources
if (hasReachedVehicleLimit()) {
    // Limite atteinte
}

// Statut abonnement
if (isCompanySubscriptionActive()) {
    // Abonnement actif
}

$daysLeft = getTrialDaysRemaining();
```

### Dans les modèles

Tous les modèles suivent ce pattern:

```php
class MyModel {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();

        // Vérification obligatoire
        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    /**
     * Filtre company pour SQL
     */
    private function getCompanyFilter($tableAlias = 't') {
        if (isSuperAdmin()) {
            return '1=1'; // Pas de filtre pour super admins
        }
        return "{$tableAlias}.company_id = :company_id";
    }

    /**
     * Lier company_id
     */
    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    // Exemple de méthode
    public function getAll() {
        $filter = $this->getCompanyFilter('t');

        $this->db->query("SELECT * FROM my_table t WHERE {$filter}");
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    // Exemple de création
    public function create($data) {
        $this->db->query("INSERT INTO my_table
            (company_id, field1, field2)
            VALUES (:company_id, :field1, :field2)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':field1', $data['field1']);
        $this->db->bind(':field2', $data['field2']);

        return $this->db->execute();
    }
}
```

### Dans les contrôleurs

Les contrôleurs n'ont pas besoin de modifications spéciales. Le modèle gère automatiquement le filtrage:

```php
class MyController extends Controller {
    private $myModel;

    public function __construct() {
        requireModule('my_module'); // Vérification module
        $this->myModel = $this->model('MyModel');
        // Le modèle charge automatiquement getCurrentCompanyId()
    }

    public function index() {
        // Les données sont automatiquement filtrées par company
        $data = [
            'items' => $this->myModel->getAll() // Seulement les données de cette company
        ];

        $this->view('my/index', $data);
    }
}
```

## Super Admin

Le système supporte des super administrateurs qui peuvent:
- Voir les données de toutes les entreprises
- Gérer les entreprises (CRUD)
- Ne sont pas liés à une entreprise spécifique

Pour créer un super admin:

```sql
UPDATE users SET is_super_admin = 1, company_id = NULL WHERE id = X;
```

Ou en PHP:
```php
$_SESSION['is_super_admin'] = true;
```

## Migration

Pour appliquer le multi-tenancy sur une base existante:

```bash
mysql -u root -p digiparc < database/migration_multi_tenant.sql
```

Cette migration:
1. Crée la table `companies`
2. Ajoute `company_id` à toutes les tables
3. Crée une entreprise de démo
4. Assigne les données existantes à cette entreprise
5. Ajoute les contraintes et index nécessaires

## Gestion des entreprises

### Créer une nouvelle entreprise

```php
$db = new Database();
$db->query("INSERT INTO companies
    (company_name, company_code, email, phone, country, subscription_status, max_users, max_vehicles)
    VALUES (:name, :code, :email, :phone, :country, 'trial', :users, :vehicles)");

$db->bind(':name', 'Ma Société');
$db->bind(':code', 'MYSOC001');
$db->bind(':email', 'contact@mysoc.tn');
$db->bind(':phone', '+216 XX XXX XXX');
$db->bind(':country', 'Tunisia');
$db->bind(':users', 10);
$db->bind(':vehicles', 20);

$companyId = $db->execute() ? $db->lastInsertId() : false;
```

### Charger une entreprise dans la session

```php
// Lors de la connexion
if (loadCompanyIntoSession($companyId)) {
    // Company chargée avec succès
    // $_SESSION['company_id'] et $_SESSION['company_data'] sont définis
}

// Pour changer d'entreprise (super admin)
clearCompanyFromSession();
loadCompanyIntoSession($newCompanyId);
```

## Sécurité

### Validation d'accès

Toujours valider que les données appartiennent à l'entreprise:

```php
// Dans un contrôleur
public function view($id) {
    $item = $this->myModel->getById($id);

    if (!$item) {
        flash('error', 'Élément non trouvé');
        redirect('my/index');
    }

    // Si le modèle est bien implémenté, $item est déjà filtré par company_id
    // Sinon, vérification manuelle:
    if (!validateCompanyAccess($item['company_id'])) {
        redirect('my/index');
    }

    // ...
}
```

### Contraintes de base

Les foreign keys garantissent l'intégrité:
- `ON DELETE CASCADE` : Suppression de la company = suppression de toutes ses données
- Les index sur `company_id` optimisent les performances

## Limites et quotas

Chaque entreprise a des limites configurables:

```php
// Vérifier avant création
if (hasReachedVehicleLimit()) {
    flash('error', 'Limite de véhicules atteinte. Mettez à niveau votre abonnement.');
    redirect('subscription-manager');
}

// Créer le véhicule...
```

Limites disponibles:
- `max_users` : Nombre maximum d'utilisateurs
- `max_vehicles` : Nombre maximum de véhicules
- `max_drivers` : Nombre maximum de conducteurs

## Abonnements

Le système multi-tenant s'intègre avec le système d'abonnements:

```php
// Vérifier le statut
$status = getCompanySubscriptionStatus();
// Valeurs: 'trial', 'active', 'suspended', 'cancelled', 'expired'

// Vérifier si actif
if (!isCompanySubscriptionActive()) {
    flash('warning', 'Votre abonnement a expiré');
    redirect('subscription-manager');
}

// Jours restants de trial
$days = getTrialDaysRemaining();
if ($days > 0 && $days <= 7) {
    flash('warning', "Il vous reste {$days} jours de période d'essai");
}
```

## Personnalisation par entreprise

### Paramètres

Chaque entreprise peut avoir ses propres paramètres:

```php
// Devise
$amount = 1500;
$formatted = formatCompanyCurrency($amount); // "1,500.00 TND"

// Dates
$date = '2024-01-15 14:30:00';
$formatted = formatCompanyDate($date, true); // "15/01/2024 14:30"

// Couleurs
$primaryColor = getCompanyPrimaryColor(); // "#007bff"

// Logo
$logoUrl = getCompanyLogo(); // URL du logo ou null
```

### Branding

Les vues peuvent utiliser les couleurs de l'entreprise:

```php
<style>
    :root {
        --company-primary: <?php echo getCompanyPrimaryColor(); ?>;
        --company-secondary: <?php echo getCompanySetting('secondary_color', '#6c757d'); ?>;
    }
</style>
```

## Performance

### Indexes

Tous les filtres `company_id` utilisent des index:
```sql
CREATE INDEX idx_company ON table_name(company_id);
CREATE INDEX idx_company_status ON table_name(company_id, status);
CREATE INDEX idx_company_date ON table_name(company_id, created_at);
```

### Cache

Pour de meilleures performances, considérer le cache des données company:

```php
// Dans session (déjà fait)
$_SESSION['company_data'] // Données de l'entreprise

// Pour données fréquentes
$_SESSION['company_settings'] = [
    'timezone' => 'Africa/Tunis',
    'currency' => 'TND',
    // ...
];
```

## Best Practices

### 1. Toujours utiliser les helpers

```php
// ❌ Mauvais
$companyId = $_SESSION['company_id'];

// ✅ Bon
$companyId = getCurrentCompanyId();
```

### 2. Filtrer dans le modèle, pas le contrôleur

```php
// ❌ Mauvais - dans contrôleur
$vehicles = $this->vehicleModel->getAll();
$filtered = array_filter($vehicles, function($v) {
    return $v['company_id'] == getCurrentCompanyId();
});

// ✅ Bon - dans modèle
$vehicles = $this->vehicleModel->getAll(); // Déjà filtré
```

### 3. Toujours inclure company_id dans les INSERT

```php
// ✅ Bon
$this->db->query("INSERT INTO my_table (company_id, name)
    VALUES (:company_id, :name)");
$this->db->bind(':company_id', $this->companyId);
```

### 4. Utiliser getCompanyFilter() pour cohérence

```php
// ✅ Bon - méthode réutilisable
$filter = $this->getCompanyFilter('t');
$this->db->query("SELECT * FROM my_table t WHERE {$filter}");
$this->bindCompanyId();
```

### 5. Gérer les super admins

```php
// ✅ Bon
if (isSuperAdmin()) {
    // Pas de filtre company
    return '1=1';
}
return "t.company_id = :company_id";
```

## Tests

Pour tester le multi-tenant:

```php
// Test isolation
$_SESSION['company_id'] = 1;
$items1 = $model->getAll();

$_SESSION['company_id'] = 2;
$items2 = $model->getAll();

// items1 et items2 ne doivent pas avoir d'éléments en commun

// Test super admin
$_SESSION['is_super_admin'] = true;
$allItems = $model->getAll(); // Toutes les entreprises
```

## Troubleshooting

### "Company context required"

Assurez-vous que:
1. L'utilisateur est connecté
2. `$_SESSION['company_id']` est défini
3. Ou l'utilisateur est super admin

### Pas de données affichées

Vérifiez:
1. `company_id` est bien défini dans la session
2. Les données ont le bon `company_id` en base
3. Le filtre SQL est correctement appliqué

### Performance lente

Vérifiez les index:
```sql
SHOW INDEX FROM table_name;
```

Ajoutez si manquant:
```sql
CREATE INDEX idx_company ON table_name(company_id);
```

## Conclusion

Le système multi-tenant de DigiParc offre:
- ✅ Isolation complète des données
- ✅ Support super admin
- ✅ Gestion des quotas
- ✅ Intégration abonnements
- ✅ Personnalisation par entreprise
- ✅ Performances optimisées
- ✅ Sécurité renforcée

Tous les nouveaux modules doivent suivre ce pattern pour garantir l'isolation des données.
