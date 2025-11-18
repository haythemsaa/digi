<?php
$data['title'] = 'Edit Vehicle';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-edit me-2"></i>Edit Vehicle: <?php echo $data['vehicle']['registration_number']; ?></h4>
                <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Fleet
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/vehicles/edit/<?php echo $data['vehicle']['id']; ?>">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                                <input type="text" name="registration_number" class="form-control" value="<?php echo $data['vehicle']['registration_number']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">VIN</label>
                                <input type="text" name="vin" class="form-control" value="<?php echo $data['vehicle']['vin'] ?? ''; ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    <option value="truck" <?php echo $data['vehicle']['type'] === 'truck' ? 'selected' : ''; ?>>Truck</option>
                                    <option value="van" <?php echo $data['vehicle']['type'] === 'van' ? 'selected' : ''; ?>>Van</option>
                                    <option value="car" <?php echo $data['vehicle']['type'] === 'car' ? 'selected' : ''; ?>>Car</option>
                                    <option value="bus" <?php echo $data['vehicle']['type'] === 'bus' ? 'selected' : ''; ?>>Bus</option>
                                    <option value="trailer" <?php echo $data['vehicle']['type'] === 'trailer' ? 'selected' : ''; ?>>Trailer</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Make <span class="text-danger">*</span></label>
                                <input type="text" name="make" class="form-control" value="<?php echo $data['vehicle']['make']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" name="model" class="form-control" value="<?php echo $data['vehicle']['model']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                <input type="number" name="year" class="form-control" value="<?php echo $data['vehicle']['year']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Color</label>
                                <input type="text" name="color" class="form-control" value="<?php echo $data['vehicle']['color'] ?? ''; ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fuel Type <span class="text-danger">*</span></label>
                                <select name="fuel_type" class="form-control" required>
                                    <option value="diesel" <?php echo $data['vehicle']['fuel_type'] === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                                    <option value="gasoline" <?php echo $data['vehicle']['fuel_type'] === 'gasoline' ? 'selected' : ''; ?>>Gasoline</option>
                                    <option value="electric" <?php echo $data['vehicle']['fuel_type'] === 'electric' ? 'selected' : ''; ?>>Electric</option>
                                    <option value="hybrid" <?php echo $data['vehicle']['fuel_type'] === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Current Mileage (km) <span class="text-danger">*</span></label>
                                <input type="number" name="current_mileage" class="form-control" value="<?php echo $data['vehicle']['current_mileage']; ?>" required>
                            </div>

                            <!-- Status & Ownership -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Status & Ownership</h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="active" <?php echo $data['vehicle']['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="maintenance" <?php echo $data['vehicle']['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="inactive" <?php echo $data['vehicle']['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="sold" <?php echo $data['vehicle']['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ownership Type</label>
                                <select name="ownership_type" class="form-control">
                                    <option value="owned" <?php echo $data['vehicle']['ownership_type'] === 'owned' ? 'selected' : ''; ?>>Owned</option>
                                    <option value="leased" <?php echo $data['vehicle']['ownership_type'] === 'leased' ? 'selected' : ''; ?>>Leased</option>
                                    <option value="rented" <?php echo $data['vehicle']['ownership_type'] === 'rented' ? 'selected' : ''; ?>>Rented</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control" value="<?php echo $data['vehicle']['purchase_date']; ?>">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"><?php echo $data['vehicle']['notes'] ?? ''; ?></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Vehicle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
