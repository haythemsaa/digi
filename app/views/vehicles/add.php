<?php
$data['title'] = 'Add Vehicle';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Vehicle</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo APP_URL; ?>/vehicles/add" method="POST" enctype="multipart/form-data">
                                <!-- Basic Information -->
                                <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                                        <input type="text"
                                               name="registration_number"
                                               class="form-control"
                                               value="<?php echo $data['registration_number'] ?? ''; ?>"
                                               required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">VIN (Vehicle Identification Number)</label>
                                        <input type="text"
                                               name="vin"
                                               class="form-control"
                                               value="<?php echo $data['vin'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Brand <span class="text-danger">*</span></label>
                                        <input type="text"
                                               name="brand"
                                               class="form-control"
                                               value="<?php echo $data['brand'] ?? ''; ?>"
                                               required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Model <span class="text-danger">*</span></label>
                                        <input type="text"
                                               name="model"
                                               class="form-control"
                                               value="<?php echo $data['model'] ?? ''; ?>"
                                               required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Year</label>
                                        <input type="number"
                                               name="year"
                                               class="form-control"
                                               min="1900"
                                               max="<?php echo date('Y') + 1; ?>"
                                               value="<?php echo $data['year'] ?? date('Y'); ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Color</label>
                                        <input type="text"
                                               name="color"
                                               class="form-control"
                                               value="<?php echo $data['color'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            <option value="">Select Type</option>
                                            <option value="car" <?php echo (isset($data['type']) && $data['type'] === 'car') ? 'selected' : ''; ?>>Car</option>
                                            <option value="truck" <?php echo (isset($data['type']) && $data['type'] === 'truck') ? 'selected' : ''; ?>>Truck</option>
                                            <option value="van" <?php echo (isset($data['type']) && $data['type'] === 'van') ? 'selected' : ''; ?>>Van</option>
                                            <option value="bus" <?php echo (isset($data['type']) && $data['type'] === 'bus') ? 'selected' : ''; ?>>Bus</option>
                                            <option value="motorcycle" <?php echo (isset($data['type']) && $data['type'] === 'motorcycle') ? 'selected' : ''; ?>>Motorcycle</option>
                                            <option value="trailer" <?php echo (isset($data['type']) && $data['type'] === 'trailer') ? 'selected' : ''; ?>>Trailer</option>
                                            <option value="special" <?php echo (isset($data['type']) && $data['type'] === 'special') ? 'selected' : ''; ?>>Special</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Fuel Type <span class="text-danger">*</span></label>
                                        <select name="fuel_type" class="form-select" required>
                                            <option value="">Select Fuel</option>
                                            <option value="gasoline" <?php echo (isset($data['fuel_type']) && $data['fuel_type'] === 'gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                                            <option value="diesel" <?php echo (isset($data['fuel_type']) && $data['fuel_type'] === 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                                            <option value="electric" <?php echo (isset($data['fuel_type']) && $data['fuel_type'] === 'electric') ? 'selected' : ''; ?>>Electric</option>
                                            <option value="hybrid" <?php echo (isset($data['fuel_type']) && $data['fuel_type'] === 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                            <option value="lpg" <?php echo (isset($data['fuel_type']) && $data['fuel_type'] === 'lpg') ? 'selected' : ''; ?>>LPG</option>
                                            <option value="cng" <?php echo (isset($data['fuel_type']) && $data['fuel_type'] === 'cng') ? 'selected' : ''; ?>>CNG</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?php echo (isset($data['status']) && $data['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="maintenance" <?php echo (isset($data['status']) && $data['status'] === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                            <option value="inactive" <?php echo (isset($data['status']) && $data['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Technical Specifications -->
                                <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-cogs me-2"></i>Technical Specifications</h6>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Engine Capacity (L)</label>
                                        <input type="number"
                                               name="engine_capacity"
                                               class="form-control"
                                               step="0.1"
                                               value="<?php echo $data['engine_capacity'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Power (HP)</label>
                                        <input type="number"
                                               name="power"
                                               class="form-control"
                                               value="<?php echo $data['power'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Transmission</label>
                                        <select name="transmission" class="form-select">
                                            <option value="">Select</option>
                                            <option value="manual">Manual</option>
                                            <option value="automatic">Automatic</option>
                                            <option value="semi-automatic">Semi-Automatic</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Fuel Tank (L)</label>
                                        <input type="number"
                                               name="fuel_tank_capacity"
                                               class="form-control"
                                               step="0.1"
                                               value="<?php echo $data['fuel_tank_capacity'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Seats</label>
                                        <input type="number"
                                               name="seats"
                                               class="form-control"
                                               value="<?php echo $data['seats'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Doors</label>
                                        <input type="number"
                                               name="doors"
                                               class="form-control"
                                               value="<?php echo $data['doors'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Weight (kg)</label>
                                        <input type="number"
                                               name="weight"
                                               class="form-control"
                                               step="0.01"
                                               value="<?php echo $data['weight'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Load Capacity (kg)</label>
                                        <input type="number"
                                               name="load_capacity"
                                               class="form-control"
                                               step="0.01"
                                               value="<?php echo $data['load_capacity'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Odometer (km)</label>
                                        <input type="number"
                                               name="odometer"
                                               class="form-control"
                                               value="<?php echo $data['odometer'] ?? 0; ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">GPS Device ID</label>
                                        <input type="text"
                                               name="gps_device_id"
                                               class="form-control"
                                               value="<?php echo $data['gps_device_id'] ?? ''; ?>">
                                    </div>
                                </div>

                                <!-- Purchase & Insurance -->
                                <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-file-invoice-dollar me-2"></i>Purchase & Insurance</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Purchase Date</label>
                                        <input type="date"
                                               name="purchase_date"
                                               class="form-control"
                                               value="<?php echo $data['purchase_date'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Purchase Price</label>
                                        <input type="number"
                                               name="purchase_price"
                                               class="form-control"
                                               step="0.01"
                                               value="<?php echo $data['purchase_price'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Current Value</label>
                                        <input type="number"
                                               name="current_value"
                                               class="form-control"
                                               step="0.01"
                                               value="<?php echo $data['current_value'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Insurance Company</label>
                                        <input type="text"
                                               name="insurance_company"
                                               class="form-control"
                                               value="<?php echo $data['insurance_company'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Insurance Policy Number</label>
                                        <input type="text"
                                               name="insurance_policy"
                                               class="form-control"
                                               value="<?php echo $data['insurance_policy'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Insurance Expiry</label>
                                        <input type="date"
                                               name="insurance_expiry"
                                               class="form-control"
                                               value="<?php echo $data['insurance_expiry'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Registration Expiry</label>
                                        <input type="date"
                                               name="registration_expiry"
                                               class="form-control"
                                               value="<?php echo $data['registration_expiry'] ?? ''; ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Technical Control Expiry</label>
                                        <input type="date"
                                               name="technical_control_expiry"
                                               class="form-control"
                                               value="<?php echo $data['technical_control_expiry'] ?? ''; ?>">
                                    </div>
                                </div>

                                <!-- Additional Info -->
                                <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-sticky-note me-2"></i>Additional Information</h6>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes"
                                                  class="form-control"
                                                  rows="3"><?php echo $data['notes'] ?? ''; ?></textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Vehicle Photo</label>
                                        <input type="file"
                                               name="photo"
                                               class="form-control"
                                               accept="image/*">
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Vehicle
                                    </button>
                                    <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
