<?php
$data['title'] = 'Vehicle Details';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-truck me-2"></i><?php echo $data['vehicle']['registration_number']; ?></h4>
                <div>
                    <a href="<?php echo APP_URL; ?>/vehicles/edit/<?php echo $data['vehicle']['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Vehicle Information -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Vehicle Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Registration Number:</strong><br>
                                    <?php echo $data['vehicle']['registration_number']; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>VIN:</strong><br>
                                    <?php echo $data['vehicle']['vin'] ?? 'N/A'; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Type:</strong><br>
                                    <span class="badge bg-info"><?php echo ucfirst($data['vehicle']['type']); ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Make & Model:</strong><br>
                                    <?php echo $data['vehicle']['make'] . ' ' . $data['vehicle']['model']; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Year:</strong><br>
                                    <?php echo $data['vehicle']['year']; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Color:</strong><br>
                                    <?php echo $data['vehicle']['color'] ?? 'N/A'; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Current Mileage:</strong><br>
                                    <?php echo number_format($data['vehicle']['current_mileage']); ?> km
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Fuel Type:</strong><br>
                                    <?php echo ucfirst($data['vehicle']['fuel_type']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($data['vehicle']['notes']): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                        </div>
                        <div class="card-body">
                            <?php echo nl2br($data['vehicle']['notes']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Status & Quick Actions -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Status</h5>
                        </div>
                        <div class="card-body">
                            <p>
                                <strong>Status:</strong><br>
                                <span class="badge bg-<?php echo $data['vehicle']['status'] === 'active' ? 'success' : ($data['vehicle']['status'] === 'maintenance' ? 'warning' : 'secondary'); ?>">
                                    <?php echo ucfirst($data['vehicle']['status']); ?>
                                </span>
                            </p>
                            <p>
                                <strong>Ownership:</strong><br>
                                <?php echo ucfirst($data['vehicle']['ownership_type']); ?>
                            </p>
                            <p class="mb-0">
                                <strong>Purchase Date:</strong><br>
                                <?php echo date('d/m/Y', strtotime($data['vehicle']['purchase_date'])); ?>
                            </p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="<?php echo APP_URL; ?>/tracking?vehicle=<?php echo $data['vehicle']['id']; ?>" class="btn btn-info btn-sm w-100 mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>Track Vehicle
                            </a>
                            <a href="<?php echo APP_URL; ?>/maintenance/addWorkOrder?vehicle=<?php echo $data['vehicle']['id']; ?>" class="btn btn-warning btn-sm w-100 mb-2">
                                <i class="fas fa-wrench me-1"></i>Create Work Order
                            </a>
                            <a href="<?php echo APP_URL; ?>/vehicles/addFuel/<?php echo $data['vehicle']['id']; ?>" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-gas-pump me-1"></i>Add Fuel Entry
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
