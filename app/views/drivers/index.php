<?php
$data['title'] = 'Drivers & HR';
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
                            <h6>Total Drivers</h6>
                            <h2><?php echo count($data['drivers']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Active</h6>
                            <h2><?php echo count(array_filter($data['drivers'], fn($d) => $d['status'] === 'active')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6>Expiring Licenses</h6>
                            <h2><?php echo count($data['expiring_licenses']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6>On Leave</h6>
                            <h2><?php echo count(array_filter($data['drivers'], fn($d) => $d['status'] === 'on_leave')); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <a href="<?php echo APP_URL; ?>/drivers/add" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add Driver
                    </a>
                    <a href="<?php echo APP_URL; ?>/drivers/infractions" class="btn btn-outline-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Infractions
                    </a>
                </div>
            </div>

            <!-- Expiring Licenses Alert -->
            <?php if (count($data['expiring_licenses']) > 0): ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-id-card me-2"></i>Licenses Expiring Soon</h5>
                <ul class="mb-0">
                    <?php foreach ($data['expiring_licenses'] as $exp): ?>
                        <li>
                            <strong><?php echo $exp['first_name'] . ' ' . $exp['last_name']; ?></strong> -
                            License expires: <?php echo date('d/m/Y', strtotime($exp['license_expiry_date'])); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Drivers Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Drivers & Personnel</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>License Number</th>
                                    <th>License Type</th>
                                    <th>License Expiry</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['drivers'] as $driver): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $driver['first_name'] . ' ' . $driver['last_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $driver['email']; ?></small>
                                        </td>
                                        <td><?php echo $driver['license_number']; ?></td>
                                        <td><?php echo $driver['license_type']; ?></td>
                                        <td>
                                            <?php if ($driver['license_expiry_date']): ?>
                                                <?php
                                                $expiry = strtotime($driver['license_expiry_date']);
                                                $today = time();
                                                $daysLeft = floor(($expiry - $today) / 86400);
                                                $badgeClass = $daysLeft < 30 ? 'danger' : ($daysLeft < 90 ? 'warning' : 'success');
                                                ?>
                                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                                    <?php echo date('d/m/Y', $expiry); ?>
                                                    (<?php echo $daysLeft; ?> days)
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $driver['phone']; ?></td>
                                        <td>
                                            <?php
                                            $statusColors = ['active' => 'success', 'on_leave' => 'warning', 'suspended' => 'danger', 'terminated' => 'dark'];
                                            $scolor = $statusColors[$driver['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $scolor; ?>"><?php echo ucfirst(str_replace('_', ' ', $driver['status'])); ?></span>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/drivers/view/<?php echo $driver['user_id']; ?>" class="btn btn-sm btn-info">
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
