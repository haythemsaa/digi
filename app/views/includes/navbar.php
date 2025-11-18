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
