<?php
$data['title'] = 'Fleet Management';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="<?php echo APP_URL; ?>/vehicles/add" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add New Vehicle
                    </a>
                    <a href="<?php echo APP_URL; ?>/vehicles/import" class="btn btn-outline-secondary">
                        <i class="fas fa-file-import me-2"></i>Import
                    </a>
                </div>
                <div>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i>Export
                    </button>
                </div>
            </div>

            <!-- Vehicles Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-car me-2"></i>All Vehicles</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Registration</th>
                                    <th>Brand & Model</th>
                                    <th>Type</th>
                                    <th>Fuel</th>
                                    <th>Year</th>
                                    <th>Odometer (km)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['vehicles'] as $vehicle): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $vehicle['registration_number']; ?></strong>
                                        </td>
                                        <td><?php echo $vehicle['brand'] . ' ' . $vehicle['model']; ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst($vehicle['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo ucfirst($vehicle['fuel_type']); ?></td>
                                        <td><?php echo $vehicle['year'] ?? '-'; ?></td>
                                        <td><?php echo number_format($vehicle['odometer']); ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'active' => 'success',
                                                'maintenance' => 'warning',
                                                'repair' => 'danger',
                                                'inactive' => 'secondary',
                                                'sold' => 'info',
                                                'accident' => 'dark'
                                            ];
                                            $statusColor = $statusColors[$vehicle['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $statusColor; ?>">
                                                <?php echo ucfirst($vehicle['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>/vehicles/view/<?php echo $vehicle['id']; ?>"
                                                   class="btn btn-info"
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/vehicles/edit/<?php echo $vehicle['id']; ?>"
                                                   class="btn btn-warning"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                                    <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)"
                                                            class="btn btn-danger"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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

<script>
function deleteVehicle(id) {
    confirmDelete('This vehicle and all related data will be permanently deleted.').then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo APP_URL; ?>/vehicles/delete/' + id;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
