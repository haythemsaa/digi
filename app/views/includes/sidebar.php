<div class="col-md-3 col-lg-2 px-0 sidebar">
    <div class="d-flex flex-column h-100">
        <!-- Logo -->
        <div class="p-4 text-center border-bottom border-white border-opacity-25">
            <h3 class="text-white mb-0"><i class="fas fa-truck-moving me-2"></i>DigiParc</h3>
            <small class="text-white-50">Fleet Management</small>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow-1 mt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'dashboard') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>

                <!-- Fleet Management -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'vehicles') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/vehicles">
                        <i class="fas fa-car"></i> Vehicles
                    </a>
                </li>

                <!-- GPS & Tracking -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'tracking') ? 'active' : ''; ?> <?php echo !hasModuleAccess('gps') ? 'text-muted' : ''; ?>"
                       href="<?php echo hasModuleAccess('gps') ? APP_URL . '/tracking' : APP_URL . '/subscription-manager'; ?>">
                        <i class="fas fa-map-marked-alt"></i> GPS Tracking
                        <?php if (!hasModuleAccess('gps')): ?>
                            <i class="fas fa-lock text-warning float-right"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Transport Orders -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'transport') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/transport">
                        <i class="fas fa-shipping-fast"></i> Transport
                    </a>
                </li>

                <!-- Maintenance -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'maintenance') ? 'active' : ''; ?> <?php echo !hasModuleAccess('maintenance') ? 'text-muted' : ''; ?>"
                       href="<?php echo hasModuleAccess('maintenance') ? APP_URL . '/maintenance' : APP_URL . '/subscription-manager'; ?>">
                        <i class="fas fa-tools"></i> Maintenance
                        <?php if (!hasModuleAccess('maintenance')): ?>
                            <i class="fas fa-lock text-warning float-right"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Drivers & HR -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'drivers') ? 'active' : ''; ?> <?php echo !hasModuleAccess('hr') ? 'text-muted' : ''; ?>"
                       href="<?php echo hasModuleAccess('hr') ? APP_URL . '/drivers' : APP_URL . '/subscription-manager'; ?>">
                        <i class="fas fa-id-card"></i> Drivers & HR
                        <?php if (!hasModuleAccess('hr')): ?>
                            <i class="fas fa-lock text-warning float-right"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Financial -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'financial') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/financial">
                        <i class="fas fa-dollar-sign"></i> Financial
                    </a>
                </li>

                <!-- Inventory -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'inventory') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/inventory">
                        <i class="fas fa-boxes"></i> Inventory
                    </a>
                </li>

                <!-- Procurement -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'procurement') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/procurement">
                        <i class="fas fa-shopping-cart"></i> Procurement
                    </a>
                </li>

                <!-- Purchase Requests -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'purchase_requests') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/purchase_requests">
                        <i class="fas fa-file-invoice"></i> Purchase Requests
                    </a>
                </li>

                <!-- Stock Management -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'stock') ? 'active' : ''; ?> <?php echo !hasModuleAccess('stocks') ? 'text-muted' : ''; ?>"
                       href="<?php echo hasModuleAccess('stocks') ? APP_URL . '/stock' : APP_URL . '/subscription-manager'; ?>">
                        <i class="fas fa-warehouse"></i> Stock Management
                        <?php if (!hasModuleAccess('stocks')): ?>
                            <i class="fas fa-lock text-warning float-right"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Rental -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'rental') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/rental">
                        <i class="fas fa-key"></i> Vehicle Rental
                    </a>
                </li>

                <!-- Cash Management -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'cash') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/cash">
                        <i class="fas fa-cash-register"></i> Cash Management
                    </a>
                </li>

                <!-- TCO Calculator -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'tco') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/tco">
                        <i class="fas fa-calculator"></i> TCO Calculator
                    </a>
                </li>

                <!-- Smart Delivery AI -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'smart_delivery') ? 'active' : ''; ?> <?php echo !hasModuleAccess('delivery') ? 'text-muted' : ''; ?>"
                       href="<?php echo hasModuleAccess('delivery') ? APP_URL . '/smart_delivery' : APP_URL . '/subscription-manager'; ?>">
                        <i class="fas fa-brain"></i> Smart Delivery AI
                        <?php if (!hasModuleAccess('delivery')): ?>
                            <i class="fas fa-lock text-warning float-right"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Passenger Transport (Taxi & Bus) -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'passenger_transport') ? 'active' : ''; ?> <?php echo !hasModuleAccess('taxi') ? 'text-muted' : ''; ?>"
                       href="<?php echo hasModuleAccess('taxi') ? APP_URL . '/passenger_transport' : APP_URL . '/subscription-manager'; ?>">
                        <i class="fas fa-taxi"></i> Transport Voyageurs
                        <?php if (!hasModuleAccess('taxi')): ?>
                            <i class="fas fa-lock text-warning float-right"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Reports -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'reports') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/reports">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>

                <!-- My Subscription -->
                <li class="nav-item mt-3 pt-3 border-top border-white border-opacity-25">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'my_subscription') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/subscription-manager/mySubscription">
                        <i class="fas fa-star text-warning"></i> Mon Abonnement
                    </a>
                </li>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Subscription Management (Admin) -->
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'subscriptions') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/subscription-manager/modules">
                        <i class="fas fa-puzzle-piece"></i> Modules & Packs
                    </a>
                </li>
                <!-- Settings (Admin only) -->
                <li class="nav-item mt-3 pt-3 border-top border-white border-opacity-25">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'users') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/users">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($data['active_menu']) && $data['active_menu'] === 'settings') ? 'active' : ''; ?>"
                       href="<?php echo APP_URL; ?>/settings">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Footer -->
        <div class="p-3 text-center border-top border-white border-opacity-25">
            <small class="text-white-50">Version <?php echo APP_VERSION; ?></small>
        </div>
    </div>
</div>
