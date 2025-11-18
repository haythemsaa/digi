<?php
$data['title'] = 'Add Driver';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-user-plus me-2"></i>Add New Driver</h4>
                <a href="<?php echo APP_URL; ?>/drivers" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Drivers
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/drivers/add">
                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Emergency Contact</label>
                                <input type="text" name="emergency_contact" class="form-control">
                            </div>

                            <!-- License Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">License Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">License Number <span class="text-danger">*</span></label>
                                <input type="text" name="license_number" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">License Type</label>
                                <select name="license_type" class="form-control">
                                    <option value="B">B - Light Vehicles</option>
                                    <option value="C">C - Heavy Vehicles</option>
                                    <option value="D">D - Passenger Transport</option>
                                    <option value="E">E - Trailer</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">License Issue Date</label>
                                <input type="date" name="license_issue_date" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">License Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" name="license_expiry_date" class="form-control" required>
                            </div>

                            <!-- Employment Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Employment Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hire Date</label>
                                <input type="date" name="hire_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employment Type</label>
                                <select name="employment_type" class="form-control">
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="contract">Contract</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salary (TND/month)</label>
                                <input type="number" name="salary" class="form-control" step="0.01">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/drivers" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Driver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
