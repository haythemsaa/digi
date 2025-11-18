<?php
$data['title'] = 'View Purchase Order';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-file-invoice me-2"></i>Purchase Order: <?php echo $data['purchase_order']['po_number']; ?></h4>
                <div>
                    <button onclick="window.print()" class="btn btn-secondary me-2">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                    <a href="<?php echo APP_URL; ?>/procurement" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- PO Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-primary">Purchase Order</h5>
                            <p class="mb-1"><strong>PO Number:</strong> <?php echo $data['purchase_order']['po_number']; ?></p>
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('d/m/Y', strtotime($data['purchase_order']['order_date'])); ?></p>
                            <p class="mb-1">
                                <strong>Status:</strong>
                                <?php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'sent' => 'info',
                                    'confirmed' => 'primary',
                                    'received' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $color = $statusColors[$data['purchase_order']['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($data['purchase_order']['status']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted">Supplier Information</h6>
                            <p class="mb-1"><strong><?php echo $data['purchase_order']['company_name']; ?></strong></p>
                            <?php if ($data['purchase_order']['address']): ?>
                            <p class="mb-1"><?php echo $data['purchase_order']['address']; ?></p>
                            <?php endif; ?>
                            <?php if ($data['purchase_order']['city']): ?>
                            <p class="mb-1"><?php echo $data['purchase_order']['city']; ?></p>
                            <?php endif; ?>
                            <?php if ($data['purchase_order']['phone']): ?>
                            <p class="mb-1">Tel: <?php echo $data['purchase_order']['phone']; ?></p>
                            <?php endif; ?>
                            <?php if ($data['purchase_order']['email']): ?>
                            <p class="mb-1">Email: <?php echo $data['purchase_order']['email']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Delivery Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Expected Delivery:</strong>
                                <?php if ($data['purchase_order']['expected_delivery_date']): ?>
                                    <?php echo date('d/m/Y', strtotime($data['purchase_order']['expected_delivery_date'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">Not specified</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end"><?php echo number_format($data['purchase_order']['subtotal'], 2); ?> TND</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tax (<?php echo $data['purchase_order']['tax_rate']; ?>%):</strong></td>
                                        <td class="text-end"><?php echo number_format($data['purchase_order']['tax_amount'], 2); ?> TND</td>
                                    </tr>
                                    <tr class="table-active">
                                        <td><strong>Total Amount:</strong></td>
                                        <td class="text-end"><strong><?php echo number_format($data['purchase_order']['total_amount'], 2); ?> TND</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if ($data['purchase_order']['notes']): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6>Notes:</h6>
                            <p><?php echo nl2br($data['purchase_order']['notes']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Footer -->
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <p class="text-muted small">
                                <strong>Created:</strong> <?php echo date('d/m/Y H:i', strtotime($data['purchase_order']['created_at'])); ?>
                            </p>
                        </div>
                    </div>
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
