<?php
$data['title'] = 'Procurement';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6>Total Suppliers</h6>
                            <h2><?php echo count($data['suppliers']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Purchase Orders</h6>
                            <h2><?php echo count($data['purchase_orders']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6>Pending POs</h6>
                            <h2><?php echo count(array_filter($data['purchase_orders'], fn($po) => $po['status'] === 'sent' || $po['status'] === 'confirmed')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Active Suppliers</h6>
                            <h2><?php echo count(array_filter($data['suppliers'], fn($s) => $s['status'] === 'active')); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suppliers Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Suppliers</h5>
                    <a href="<?php echo APP_URL; ?>/procurement/addSupplier" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Supplier
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Contact</th>
                                    <th>Category</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['suppliers'] as $supplier): ?>
                                    <tr>
                                        <td><strong><?php echo $supplier['company_name']; ?></strong></td>
                                        <td><?php echo $supplier['contact_person']; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo ucfirst($supplier['category']); ?></span></td>
                                        <td><?php echo $supplier['phone']; ?></td>
                                        <td><?php echo $supplier['email']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $supplier['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($supplier['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/procurement/viewSupplier/<?php echo $supplier['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Purchase Orders Section -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Purchase Orders</h5>
                    <a href="<?php echo APP_URL; ?>/procurement/addPurchaseOrder" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i>Create PO
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>PO Number</th>
                                    <th>Supplier</th>
                                    <th>Order Date</th>
                                    <th>Expected Delivery</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['purchase_orders'] as $po): ?>
                                    <tr>
                                        <td><strong><?php echo $po['po_number']; ?></strong></td>
                                        <td><?php echo $po['company_name']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($po['order_date'])); ?></td>
                                        <td>
                                            <?php if ($po['expected_delivery_date']): ?>
                                                <?php echo date('d/m/Y', strtotime($po['expected_delivery_date'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($po['total_amount'], 2); ?> TND</td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'sent' => 'info',
                                                'confirmed' => 'primary',
                                                'received' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $color = $statusColors[$po['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($po['status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/procurement/viewPO/<?php echo $po['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
