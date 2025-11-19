<?php
// Company Switcher Component - For Super Admin Navigation
if (!isSuperAdmin()) {
    return;
}

// Get current company context
$currentCompanyId = getCurrentCompanyId();
$currentCompany = null;

if ($currentCompanyId) {
    $db = new Database();
    $db->query("SELECT id, company_name, company_code, logo FROM companies WHERE id = :id");
    $db->bind(':id', $currentCompanyId);
    $currentCompany = $db->fetch();
}
?>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="companySwitcher"
       role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-building me-2"></i>
        <?php if ($currentCompany): ?>
            <span class="d-none d-md-inline"><?= htmlspecialchars($currentCompany['company_name']) ?></span>
            <small class="d-none d-lg-inline text-muted ms-1">(<?= htmlspecialchars($currentCompany['company_code']) ?>)</small>
        <?php else: ?>
            <span class="badge bg-danger">Super Admin Mode</span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="companySwitcher" style="min-width: 300px;">
        <li class="dropdown-header">
            <strong>Changer d'entreprise</strong>
        </li>
        <li><hr class="dropdown-divider"></li>

        <?php if ($currentCompany): ?>
        <li>
            <a class="dropdown-item active" href="#">
                <i class="fas fa-check-circle text-success me-2"></i>
                <?= htmlspecialchars($currentCompany['company_name']) ?>
                <br>
                <small class="text-muted ms-4"><?= htmlspecialchars($currentCompany['company_code']) ?></small>
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-danger" href="<?= APP_URL ?>/companies/switchBack">
                <i class="fas fa-sign-out-alt me-2"></i>
                Revenir en Super Admin
            </a>
        </li>
        <?php else: ?>
        <li>
            <a class="dropdown-item text-info" href="<?= APP_URL ?>/companies">
                <i class="fas fa-building me-2"></i>
                Gérer les entreprises
            </a>
        </li>
        <?php endif; ?>

        <li><hr class="dropdown-divider"></li>

        <?php
        // Get recent/active companies
        $db = new Database();
        $db->query("SELECT id, company_name, company_code, logo, status, subscription_status
                   FROM companies
                   WHERE status = 'active'
                   ORDER BY company_name
                   LIMIT 10");
        $companies = $db->fetchAll();
        ?>

        <?php if ($companies): ?>
        <li class="dropdown-header">
            <small>Entreprises Actives (<?= count($companies) ?>)</small>
        </li>
        <?php foreach ($companies as $company): ?>
            <?php if ($currentCompanyId && $company['id'] == $currentCompanyId) continue; ?>
            <li>
                <a class="dropdown-item" href="<?= APP_URL ?>/companies/switchTo/<?= $company['id'] ?>">
                    <?php if ($company['logo']): ?>
                        <img src="<?= htmlspecialchars($company['logo']) ?>" alt="" class="me-2"
                             style="width: 20px; height: 20px; object-fit: contain;">
                    <?php else: ?>
                        <i class="fas fa-building text-muted me-2"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($company['company_name']) ?>
                    <br>
                    <small class="text-muted ms-4">
                        <?= htmlspecialchars($company['company_code']) ?>
                        <?php if ($company['subscription_status'] === 'trial'): ?>
                            <span class="badge bg-info" style="font-size: 0.65rem;">Trial</span>
                        <?php endif; ?>
                    </small>
                </a>
            </li>
        <?php endforeach; ?>

        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-primary" href="<?= APP_URL ?>/companies">
                <i class="fas fa-list me-2"></i>
                Voir toutes les entreprises
            </a>
        </li>
        <?php else: ?>
        <li>
            <span class="dropdown-item text-muted">
                <em>Aucune entreprise active</em>
            </span>
        </li>
        <?php endif; ?>

        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-success" href="<?= APP_URL ?>/companies/create">
                <i class="fas fa-plus-circle me-2"></i>
                Créer une entreprise
            </a>
        </li>
    </ul>
</li>
