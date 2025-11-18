<?php
$data['title'] = 'Add User';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-user-plus me-2"></i>Add New User</h4>
                <a href="<?php echo APP_URL; ?>/users" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Users
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/users/add">
                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>

                            <!-- Account Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Account Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" minlength="6" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirm" class="form-control" minlength="6" required>
                            </div>

                            <!-- Role & Status -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Role & Status</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-control" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="manager">Manager</option>
                                    <option value="dispatcher">Dispatcher</option>
                                    <option value="driver">Driver</option>
                                    <option value="mechanic">Mechanic</option>
                                    <option value="accountant">Accountant</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Contact Information (Optional)</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile</label>
                                <input type="text" name="mobile" class="form-control">
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
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="Tunisia">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/users" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password validation
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.querySelector('input[name="password"]').value;
    const confirmPassword = document.querySelector('input[name="password_confirm"]').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
});
</script>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
