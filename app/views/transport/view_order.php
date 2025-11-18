<?php
$data['title'] = 'View Order';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-shipping-fast me-2"></i>Order: <?php echo $data['order']['order_number']; ?></h4>
                <div>
                    <button onclick="window.print()" class="btn btn-secondary me-2">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                    <a href="<?php echo APP_URL; ?>/transport" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Order Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-primary">Transport Order</h5>
                            <p class="mb-1"><strong>Order Number:</strong> <?php echo $data['order']['order_number']; ?></p>
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('d/m/Y', strtotime($data['order']['created_at'])); ?></p>
                            <p class="mb-1">
                                <strong>Status:</strong>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'assigned' => 'primary',
                                    'in_transit' => 'info',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $color = $statusColors[$data['order']['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($data['order']['status']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted">Client Information</h6>
                            <p class="mb-1">
                                <strong>
                                    <?php echo $data['order']['company_name'] ?? ($data['order']['first_name'] . ' ' . $data['order']['last_name']); ?>
                                </strong>
                            </p>
                            <?php if (isset($data['order']['client_email'])): ?>
                            <p class="mb-1">Email: <?php echo $data['order']['client_email']; ?></p>
                            <?php endif; ?>
                            <?php if (isset($data['order']['client_phone'])): ?>
                            <p class="mb-1">Tel: <?php echo $data['order']['client_phone']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Route Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><i class="fas fa-map-marker-alt text-success me-2"></i>Pickup Location</h6>
                            <p class="mb-1"><?php echo $data['order']['pickup_address']; ?></p>
                            <p class="mb-1"><?php echo $data['order']['pickup_city']; ?> <?php echo $data['order']['pickup_postal_code']; ?></p>
                            <?php if ($data['order']['pickup_date']): ?>
                            <p class="mb-1"><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($data['order']['pickup_date'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-map-marker-alt text-danger me-2"></i>Delivery Location</h6>
                            <p class="mb-1"><?php echo $data['order']['delivery_address']; ?></p>
                            <p class="mb-1"><?php echo $data['order']['delivery_city']; ?> <?php echo $data['order']['delivery_postal_code']; ?></p>
                            <?php if ($data['order']['delivery_date']): ?>
                            <p class="mb-1"><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($data['order']['delivery_date'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Cargo & Vehicle Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Cargo Information</h6>
                            <p class="mb-1"><strong>Type:</strong> <?php echo ucfirst($data['order']['cargo_type']); ?></p>
                            <?php if ($data['order']['weight']): ?>
                            <p class="mb-1"><strong>Weight:</strong> <?php echo $data['order']['weight']; ?> kg</p>
                            <?php endif; ?>
                            <?php if ($data['order']['volume']): ?>
                            <p class="mb-1"><strong>Volume:</strong> <?php echo $data['order']['volume']; ?> m³</p>
                            <?php endif; ?>
                            <?php if ($data['order']['distance']): ?>
                            <p class="mb-1"><strong>Distance:</strong> <?php echo $data['order']['distance']; ?> km</p>
                            <?php endif; ?>
                            <?php if ($data['order']['cargo_description']): ?>
                            <p class="mb-1"><strong>Description:</strong><br><?php echo $data['order']['cargo_description']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6>Assignment</h6>
                            <p class="mb-1">
                                <strong>Vehicle:</strong>
                                <?php echo $data['order']['registration_number'] ?? '<span class="text-muted">Not assigned</span>'; ?>
                            </p>
                            <p class="mb-1">
                                <strong>Driver:</strong>
                                <?php echo $data['order']['driver_name'] ?? '<span class="text-muted">Not assigned</span>'; ?>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Financial Summary -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Base Price:</strong></td>
                                        <td class="text-end"><?php echo number_format($data['order']['base_price'], 2); ?> TND</td>
                                    </tr>
                                    <?php if ($data['order']['additional_charges'] > 0): ?>
                                    <tr>
                                        <td><strong>Additional Charges:</strong></td>
                                        <td class="text-end"><?php echo number_format($data['order']['additional_charges'], 2); ?> TND</td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Tax (<?php echo $data['order']['tax_rate']; ?>%):</strong></td>
                                        <td class="text-end"><?php echo number_format($data['order']['tax_amount'], 2); ?> TND</td>
                                    </tr>
                                    <tr class="table-active">
                                        <td><strong>Total Amount:</strong></td>
                                        <td class="text-end"><strong><?php echo number_format($data['order']['total_amount'], 2); ?> TND</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if ($data['order']['notes']): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6>Notes:</h6>
                            <p><?php echo nl2br($data['order']['notes']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar, .btn, .no-print {
        display: none !important;
    }
    .main-content {
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
