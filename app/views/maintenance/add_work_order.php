<?php
$data['title'] = 'Create Work Order';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-wrench me-2"></i>Create Work Order</h4>
                <a href="<?php echo APP_URL; ?>/maintenance" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Maintenance
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/maintenance/addWorkOrder">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Work Order Details</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                                <select name="vehicle_id" class="form-control" required>
                                    <option value="">Select Vehicle</option>
                                    <?php foreach ($data['vehicles'] as $vehicle): ?>
                                        <option value="<?php echo $vehicle['id']; ?>" <?php echo (isset($_GET['vehicle']) && $_GET['vehicle'] == $vehicle['id']) ? 'selected' : ''; ?>>
                                            <?php echo $vehicle['registration_number'] . ' - ' . $vehicle['make'] . ' ' . $vehicle['model']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                                <select name="maintenance_type_id" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($data['maintenance_types'] as $type): ?>
                                        <option value="<?php echo $type['id']; ?>">
                                            <?php echo $type['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-control">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Scheduled Date</label>
                                <input type="date" name="scheduled_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Odometer Reading</label>
                                <input type="number" name="odometer_reading" class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="3" required></textarea>
                            </div>

                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Cost Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Labor Cost (TND)</label>
                                <input type="number" name="labor_cost" class="form-control" step="0.01" value="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Parts Cost (TND)</label>
                                <input type="number" name="parts_cost" class="form-control" step="0.01" value="0">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/maintenance" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Work Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
