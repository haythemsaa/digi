<?php
$data['title'] = 'Inventory Management';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6>Total Parts</h6>
                            <h2><?php echo count($data['parts']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6>Low Stock</h6>
                            <h2><?php echo count($data['low_stock']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Total Value</h6>
                            <h2>
                                <?php
                                $total = 0;
                                foreach ($data['parts'] as $p) {
                                    $total += $p['current_stock'] * $p['unit_cost'];
                                }
                                echo number_format($total, 2);
                                ?> TND
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Active Parts</h6>
                            <h2><?php echo count(array_filter($data['parts'], fn($p) => $p['status'] === 'active')); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <a href="<?php echo APP_URL; ?>/inventory/addPart" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add Part
                    </a>
                    <a href="<?php echo APP_URL; ?>/inventory/movements" class="btn btn-outline-secondary">
                        <i class="fas fa-exchange-alt me-2"></i>Stock Movements
                    </a>
                    <a href="<?php echo APP_URL; ?>/inventory/addMovement" class="btn btn-outline-success">
                        <i class="fas fa-plus me-2"></i>Record Movement
                    </a>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <?php if (count($data['low_stock']) > 0): ?>
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert</h5>
                <ul class="mb-0">
                    <?php foreach ($data['low_stock'] as $low): ?>
                        <li>
                            <strong><?php echo $low['part_name']; ?></strong> -
                            Stock: <?php echo $low['current_stock']; ?> (Min: <?php echo $low['min_stock']; ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Parts Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Parts Inventory</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Part Number</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Min Stock</th>
                                    <th>Unit Cost</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['parts'] as $part): ?>
                                    <tr>
                                        <td><strong><?php echo $part['part_number']; ?></strong></td>
                                        <td><?php echo $part['part_name']; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo ucfirst($part['category']); ?></span></td>
                                        <td>
                                            <span class="badge bg-<?php echo $part['current_stock'] <= $part['min_stock'] ? 'danger' : 'success'; ?>">
                                                <?php echo $part['current_stock']; ?> <?php echo $part['unit']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $part['min_stock']; ?></td>
                                        <td><?php echo number_format($part['unit_cost'], 2); ?> TND</td>
                                        <td><?php echo number_format($part['current_stock'] * $part['unit_cost'], 2); ?> TND</td>
                                        <td>
                                            <span class="badge bg-<?php echo $part['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($part['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/inventory/view/<?php echo $part['id']; ?>" class="btn btn-sm btn-info">
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
