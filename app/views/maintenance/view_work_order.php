<?php
$data['title'] = 'View Work Order';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-wrench me-2"></i>Work Order: <?php echo $data['work_order']['reference']; ?></h4>
                <div>
                    <button onclick="window.print()" class="btn btn-secondary me-2">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                    <a href="<?php echo APP_URL; ?>/maintenance" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Work Order Details</h5>
                            <p class="mb-1"><strong>Reference:</strong> <?php echo $data['work_order']['reference']; ?></p>
                            <p class="mb-1"><strong>Vehicle:</strong> <?php echo $data['work_order']['registration_number']; ?></p>
                            <p class="mb-1"><strong>Maintenance Type:</strong> <?php echo $data['work_order']['maintenance_type_name']; ?></p>
                            <p class="mb-1">
                                <strong>Priority:</strong>
                                <?php
                                $priorityColors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                                $color = $priorityColors[$data['work_order']['priority']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($data['work_order']['priority']); ?></span>
                            </p>
                            <p class="mb-1">
                                <strong>Status:</strong>
                                <?php
                                $statusColors = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'danger'];
                                $scolor = $statusColors[$data['work_order']['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $scolor; ?>"><?php echo ucfirst($data['work_order']['status']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Dates & Assignment</h5>
                            <?php if ($data['work_order']['scheduled_date']): ?>
                            <p class="mb-1"><strong>Scheduled:</strong> <?php echo date('d/m/Y', strtotime($data['work_order']['scheduled_date'])); ?></p>
                            <?php endif; ?>
                            <?php if ($data['work_order']['completed_date']): ?>
                            <p class="mb-1"><strong>Completed:</strong> <?php echo date('d/m/Y', strtotime($data['work_order']['completed_date'])); ?></p>
                            <?php endif; ?>
                            <?php if ($data['work_order']['odometer_reading']): ?>
                            <p class="mb-1"><strong>Odometer:</strong> <?php echo number_format($data['work_order']['odometer_reading']); ?> km</p>
                            <?php endif; ?>
                            <?php if (isset($data['work_order']['mechanic_name'])): ?>
                            <p class="mb-1"><strong>Mechanic:</strong> <?php echo $data['work_order']['mechanic_name']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6>Description</h6>
                            <p><?php echo nl2br($data['work_order']['description']); ?></p>
                        </div>
                    </div>

                    <?php if ($data['work_order']['notes']): ?>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6>Notes</h6>
                            <p><?php echo nl2br($data['work_order']['notes']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Labor Cost:</strong></td>
                                        <td class="text-end"><?php echo number_format($data['work_order']['labor_cost'], 2); ?> TND</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Parts Cost:</strong></td>
                                        <td class="text-end"><?php echo number_format($data['work_order']['parts_cost'], 2); ?> TND</td>
                                    </tr>
                                    <tr class="table-active">
                                        <td><strong>Total Cost:</strong></td>
                                        <td class="text-end">
                                            <strong><?php echo number_format($data['work_order']['total_cost'], 2); ?> TND</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar, .btn {
        display: none !important;
    }
    .main-content {
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
