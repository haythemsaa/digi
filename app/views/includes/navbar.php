<div class="topbar">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><?php echo isset($data['page_title']) ? $data['page_title'] : 'Dashboard'; ?></h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <?php if (isset($data['breadcrumbs'])): ?>
                        <?php foreach ($data['breadcrumbs'] as $breadcrumb): ?>
                            <?php if (isset($breadcrumb['active']) && $breadcrumb['active']): ?>
                                <li class="breadcrumb-item active"><?php echo $breadcrumb['title']; ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <a href="<?php echo APP_URL . '/' . $breadcrumb['url']; ?>"><?php echo $breadcrumb['title']; ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Company Switcher (Super Admin Only) -->
            <?php if (isSuperAdmin()): ?>
                <?php
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
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-building"></i>
                        <?php if ($currentCompany): ?>
                            <span class="d-none d-lg-inline"><?= htmlspecialchars($currentCompany['company_name']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger">Super Admin</span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
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
                                    <?php if ($company['subscription_status'] === 'trial'): ?>
                                        <span class="badge bg-info ms-1" style="font-size: 0.65rem;">Trial</span>
                                    <?php endif; ?>
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
                </div>
            <?php endif; ?>

            <!-- Notifications -->
            <div class="position-relative">
                <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fs-5"></i>
                    <span class="notification-badge">3</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                    <li class="dropdown-header">Notifications</li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Maintenance due for VH-001
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        Speed alert: VH-005
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="fas fa-file-invoice text-info me-2"></i>
                        New invoice created
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#">View all notifications</a></li>
                </ul>
            </div>

            <!-- User Profile -->
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)); ?>
                    </div>
                    <div class="text-start d-none d-md-block">
                        <div class="fw-bold"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></div>
                        <small class="text-muted"><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/settings"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/login/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
