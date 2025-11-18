<?php
$data['title'] = 'Add Transaction';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-plus-circle me-2"></i>Add Transaction</h4>
                <a href="<?php echo APP_URL; ?>/financial" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Financial
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/financial/addTransaction">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Transaction Details</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-control" required>
                                    <option value="">Select Account</option>
                                    <?php foreach ($data['accounts'] as $account): ?>
                                        <option value="<?php echo $account['id']; ?>">
                                            <?php echo $account['name']; ?> (<?php echo number_format($account['balance'], 2); ?> TND)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-control">
                                    <option value="fuel">Fuel</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="salary">Salary</option>
                                    <option value="transport">Transport Revenue</option>
                                    <option value="parts">Parts Purchase</option>
                                    <option value="insurance">Insurance</option>
                                    <option value="taxes">Taxes</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount (TND) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/financial" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Save Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
