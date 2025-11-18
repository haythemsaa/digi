<?php
$data['title'] = 'Create Transport Order';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-shipping-fast me-2"></i>Create Transport Order</h4>
                <a href="<?php echo APP_URL; ?>/transport" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Transport
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/transport/addOrder">
                        <div class="row">
                            <!-- Client Information -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Client Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-control" required>
                                    <option value="">Select Client</option>
                                    <?php if (isset($data['clients'])): ?>
                                        <?php foreach ($data['clients'] as $client): ?>
                                            <option value="<?php echo $client['id']; ?>">
                                                <?php echo $client['company_name'] ?? ($client['first_name'] . ' ' . $client['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="in_transit">In Transit</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                            </div>

                            <!-- Pickup Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Pickup Information</h5>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Pickup Address <span class="text-danger">*</span></label>
                                <input type="text" name="pickup_address" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pickup City <span class="text-danger">*</span></label>
                                <input type="text" name="pickup_city" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pickup Postal Code</label>
                                <input type="text" name="pickup_postal_code" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pickup Date</label>
                                <input type="date" name="pickup_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <!-- Delivery Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Delivery Information</h5>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                                <input type="text" name="delivery_address" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Delivery City <span class="text-danger">*</span></label>
                                <input type="text" name="delivery_city" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Delivery Postal Code</label>
                                <input type="text" name="delivery_postal_code" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Delivery Date</label>
                                <input type="date" name="delivery_date" class="form-control">
                            </div>

                            <!-- Cargo Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Cargo Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cargo Type</label>
                                <select name="cargo_type" class="form-control">
                                    <option value="general">General Cargo</option>
                                    <option value="fragile">Fragile</option>
                                    <option value="perishable">Perishable</option>
                                    <option value="hazardous">Hazardous</option>
                                    <option value="oversized">Oversized</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" name="weight" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Volume (m³)</label>
                                <input type="number" name="volume" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Distance (km)</label>
                                <input type="number" name="distance" class="form-control" step="0.1">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Vehicle</label>
                                <select name="vehicle_id" class="form-control">
                                    <option value="">Not assigned</option>
                                    <?php if (isset($data['vehicles'])): ?>
                                        <?php foreach ($data['vehicles'] as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>">
                                                <?php echo $vehicle['registration_number']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="cargo_description" class="form-control" rows="2"></textarea>
                            </div>

                            <!-- Pricing -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Pricing</h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Base Price (TND) <span class="text-danger">*</span></label>
                                <input type="number" name="base_price" class="form-control" step="0.01" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Additional Charges (TND)</label>
                                <input type="number" name="additional_charges" class="form-control" step="0.01" value="0">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" name="tax_rate" class="form-control" step="0.01" value="19">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/transport" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
