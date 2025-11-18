<?php
$data['title'] = 'View Supplier';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-building me-2"></i><?php echo $data['supplier']['company_name']; ?></h4>
                <a href="<?php echo APP_URL; ?>/procurement" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Procurement
                </a>
            </div>

            <div class="row">
                <!-- Supplier Information -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Supplier Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Company Name:</strong><br>
                                    <?php echo $data['supplier']['company_name']; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Tax ID:</strong><br>
                                    <?php echo $data['supplier']['tax_id'] ?? 'N/A'; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Contact Person:</strong><br>
                                    <?php echo $data['supplier']['contact_person'] ?? 'N/A'; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Category:</strong><br>
                                    <span class="badge bg-secondary"><?php echo ucfirst($data['supplier']['category']); ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Email:</strong><br>
                                    <?php if ($data['supplier']['email']): ?>
                                        <a href="mailto:<?php echo $data['supplier']['email']; ?>"><?php echo $data['supplier']['email']; ?></a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Phone:</strong><br>
                                    <?php echo $data['supplier']['phone'] ?? 'N/A'; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Mobile:</strong><br>
                                    <?php echo $data['supplier']['mobile'] ?? 'N/A'; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Payment Terms:</strong><br>
                                    <?php echo $data['supplier']['payment_terms']; ?> days
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Address:</strong><br>
                                    <?php
                                    $address = [];
                                    if ($data['supplier']['address']) $address[] = $data['supplier']['address'];
                                    if ($data['supplier']['city']) $address[] = $data['supplier']['city'];
                                    if ($data['supplier']['postal_code']) $address[] = $data['supplier']['postal_code'];
                                    if ($data['supplier']['country']) $address[] = $data['supplier']['country'];
                                    echo implode(', ', $address) ?: 'N/A';
                                    ?>
                                </div>
                                <?php if ($data['supplier']['notes']): ?>
                                <div class="col-md-12 mb-3">
                                    <strong>Notes:</strong><br>
                                    <?php echo nl2br($data['supplier']['notes']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
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
                                <span class="badge bg-<?php echo $data['supplier']['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($data['supplier']['status']); ?>
                                </span>
                            </p>
                            <p class="mb-0">
                                <strong>Created:</strong><br>
                                <small><?php echo date('d/m/Y H:i', strtotime($data['supplier']['created_at'])); ?></small>
                            </p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="<?php echo APP_URL; ?>/procurement/addPurchaseOrder" class="btn btn-success btn-sm w-100 mb-2">
                                <i class="fas fa-file-invoice me-1"></i>Create Purchase Order
                            </a>
                            <?php if ($data['supplier']['email']): ?>
                            <a href="mailto:<?php echo $data['supplier']['email']; ?>" class="btn btn-info btn-sm w-100 mb-2">
                                <i class="fas fa-envelope me-1"></i>Send Email
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
