<?php
$data['title'] = 'Add Account';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-plus-circle me-2"></i>Add New Account</h4>
                <a href="<?php echo APP_URL; ?>/financial" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Financial
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/financial/addAccount">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    <option value="bank">Bank Account</option>
                                    <option value="cash">Cash</option>
                                    <option value="credit">Credit Card</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-control">
                                    <option value="TND">TND - Tunisian Dinar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="USD">USD - US Dollar</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Opening Balance (TND)</label>
                                <input type="number" name="balance" class="form-control" step="0.01" value="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/financial" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
