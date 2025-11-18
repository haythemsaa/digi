<?php
$data['title'] = 'My Profile';
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

            <h4 class="mb-4"><i class="fas fa-user-circle me-2"></i>My Profile</h4>

            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo APP_URL; ?>/settings/profile">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?php echo $_SESSION['first_name']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?php echo $_SESSION['last_name']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $_SESSION['email']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo $_SESSION['phone'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo APP_URL; ?>/settings/changePassword" id="passwordForm">
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" minlength="6" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-lock me-1"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Info</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Username:</strong><br><?php echo $_SESSION['username']; ?></p>
                            <p><strong>Role:</strong><br>
                                <span class="badge bg-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
                            </p>
                            <p class="mb-0"><strong>Last Login:</strong><br>
                                <small><?php echo $_SESSION['last_login'] ? date('d/m/Y H:i', strtotime($_SESSION['last_login'])) : 'N/A'; ?></small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPass = document.querySelector('input[name="new_password"]').value;
    const confirmPass = document.querySelector('input[name="confirm_password"]').value;

    if (newPass !== confirmPass) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
});
</script>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
