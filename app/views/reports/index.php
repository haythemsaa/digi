<?php
$data['title'] = 'Reports & Analytics';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-car fa-3x text-primary mb-3"></i>
                            <h5>Fleet Report</h5>
                            <p class="text-muted">Complete fleet overview, vehicle statistics, and analysis</p>
                            <a href="<?php echo APP_URL; ?>/reports/fleet" class="btn btn-primary">
                                <i class="fas fa-chart-bar me-2"></i>View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-map-marked-alt fa-3x text-success mb-3"></i>
                            <h5>GPS Activity</h5>
                            <p class="text-muted">GPS tracking data, trips, and alerts analysis</p>
                            <a href="<?php echo APP_URL; ?>/reports/gpsActivity" class="btn btn-success">
                                <i class="fas fa-chart-line me-2"></i>View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-shipping-fast fa-3x text-info mb-3"></i>
                            <h5>Transport Report</h5>
                            <p class="text-muted">Orders, quotes, invoicing, and revenue analysis</p>
                            <a href="<?php echo APP_URL; ?>/reports/transport" class="btn btn-info">
                                <i class="fas fa-file-alt me-2"></i>View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tools fa-3x text-warning mb-3"></i>
                            <h5>Maintenance Report</h5>
                            <p class="text-muted">Work orders, costs, and preventive maintenance</p>
                            <a href="<?php echo APP_URL; ?>/reports/maintenance" class="btn btn-warning">
                                <i class="fas fa-wrench me-2"></i>View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign fa-3x text-danger mb-3"></i>
                            <h5>Financial Report</h5>
                            <p class="text-muted">Income, expenses, and financial overview</p>
                            <a href="<?php echo APP_URL; ?>/reports/financial" class="btn btn-danger">
                                <i class="fas fa-coins me-2"></i>View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-boxes fa-3x text-secondary mb-3"></i>
                            <h5>Custom Report</h5>
                            <p class="text-muted">Create custom reports with specific filters</p>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-sliders-h me-2"></i>Coming Soon
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-download me-2"></i>Quick Export</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo APP_URL; ?>/reports/export/fleet" class="btn btn-outline-success w-100">
                                <i class="fas fa-file-excel me-2"></i>Fleet Data (CSV)
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo APP_URL; ?>/reports/export/transport" class="btn btn-outline-success w-100">
                                <i class="fas fa-file-excel me-2"></i>Transport Data (CSV)
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo APP_URL; ?>/reports/export/maintenance" class="btn btn-outline-success w-100">
                                <i class="fas fa-file-excel me-2"></i>Maintenance Data (CSV)
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-danger w-100" disabled>
                                <i class="fas fa-file-pdf me-2"></i>PDF Export (Coming Soon)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
