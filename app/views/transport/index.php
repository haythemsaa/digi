<?php
$data['title'] = 'Transport Management';
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

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6>Total Orders</h6>
                            <h2><?php echo count($data['orders']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6>Pending</h6>
                            <h2><?php echo count(array_filter($data['orders'], fn($o) => $o['status'] === 'pending')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>In Transit</h6>
                            <h2><?php echo count(array_filter($data['orders'], fn($o) => $o['status'] === 'in_transit')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Delivered</h6>
                            <h2><?php echo count(array_filter($data['orders'], fn($o) => $o['status'] === 'delivered')); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-4">
                <div>
                    <a href="<?php echo APP_URL; ?>/transport/addOrder" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>New Order
                    </a>
                    <a href="<?php echo APP_URL; ?>/transport/quotes" class="btn btn-outline-secondary">
                        <i class="fas fa-file-alt me-2"></i>Quotes
                    </a>
                    <a href="<?php echo APP_URL; ?>/transport/clients" class="btn btn-outline-info">
                        <i class="fas fa-users me-2"></i>Clients
                    </a>
                    <a href="<?php echo APP_URL; ?>/transport/invoices" class="btn btn-outline-success">
                        <i class="fas fa-file-invoice me-2"></i>Invoices
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Transport Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Client</th>
                                    <th>Pickup</th>
                                    <th>Delivery</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['orders'] as $order): ?>
                                    <tr>
                                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                                        <td><?php echo $order['company_name'] ?? ($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                        <td><?php echo $order['pickup_city']; ?></td>
                                        <td><?php echo $order['delivery_city']; ?></td>
                                        <td><?php echo $order['registration_number'] ?? 'Not assigned'; ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'assigned' => 'primary',
                                                'in_transit' => 'info',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $color = $statusColors[$order['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($order['status']); ?></span>
                                        </td>
                                        <td><?php echo number_format($order['total_amount'], 2); ?> TND</td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/transport/viewOrder/<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
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
