<?php
$data['title'] = 'Maintenance Management';
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
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6>Pending Work Orders</h6>
                            <h2><?php echo count(array_filter($data['work_orders'], fn($wo) => $wo['status'] === 'pending')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>In Progress</h6>
                            <h2><?php echo count(array_filter($data['work_orders'], fn($wo) => $wo['status'] === 'in_progress')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6>Due Maintenances</h6>
                            <h2><?php echo count($data['due_maintenances']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Completed This Month</h6>
                            <h2><?php echo count(array_filter($data['work_orders'], fn($wo) => $wo['status'] === 'completed')); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <a href="<?php echo APP_URL; ?>/maintenance/addWorkOrder" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>New Work Order
                    </a>
                    <a href="<?php echo APP_URL; ?>/maintenance/schedules" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-alt me-2"></i>Preventive Schedules
                    </a>
                </div>
            </div>

            <!-- Due Maintenances Alert -->
            <?php if (count($data['due_maintenances']) > 0): ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Maintenances Due Soon</h5>
                <ul class="mb-0">
                    <?php foreach ($data['due_maintenances'] as $due): ?>
                        <li>
                            <strong><?php echo $due['registration_number']; ?></strong> - <?php echo $due['maintenance_type']; ?>
                            <?php if ($due['next_service_date']): ?>
                                (Due: <?php echo date('d/m/Y', strtotime($due['next_service_date'])); ?>)
                            <?php endif; ?>
                            <?php if ($due['next_service_km']): ?>
                                (At <?php echo number_format($due['next_service_km']); ?> km)
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Work Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-wrench me-2"></i>Work Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Vehicle</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Mechanic</th>
                                    <th>Total Cost</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['work_orders'] as $wo): ?>
                                    <tr>
                                        <td><strong><?php echo $wo['reference']; ?></strong></td>
                                        <td><?php echo $wo['registration_number']; ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo ucfirst($wo['type']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityColors = ['low' => 'info', 'medium' => 'warning', 'high' => 'danger', 'critical' => 'dark'];
                                            $color = $priorityColors[$wo['priority']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($wo['priority']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusColors = ['pending' => 'warning', 'scheduled' => 'info', 'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
                                            $scolor = $statusColors[$wo['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $scolor; ?>"><?php echo ucfirst($wo['status']); ?></span>
                                        </td>
                                        <td><?php echo $wo['mechanic_first'] ? $wo['mechanic_first'] . ' ' . $wo['mechanic_last'] : 'Not assigned'; ?></td>
                                        <td><?php echo number_format($wo['total_cost'], 2); ?> TND</td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/maintenance/viewWorkOrder/<?php echo $wo['id']; ?>" class="btn btn-sm btn-info">
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
