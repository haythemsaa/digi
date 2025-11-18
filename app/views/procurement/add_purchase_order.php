<?php
$data['title'] = 'Create Purchase Order';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-file-invoice me-2"></i>Create Purchase Order</h4>
                <a href="<?php echo APP_URL; ?>/procurement" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Procurement
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/procurement/addPurchaseOrder" id="poForm">
                        <div class="row">
                            <!-- Order Information -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Order Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-control" required>
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($data['suppliers'] as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>">
                                            <?php echo $supplier['company_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                    <option value="confirmed">Confirmed</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order Date <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" class="form-control">
                            </div>

                            <!-- Financial Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Financial Details</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subtotal (TND) <span class="text-danger">*</span></label>
                                <input type="number" name="subtotal" id="subtotal" class="form-control" step="0.01" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" name="tax_rate" id="tax_rate" class="form-control" step="0.01" value="19">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax Amount (TND)</label>
                                <input type="number" id="tax_amount" class="form-control" step="0.01" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Amount (TND)</label>
                                <input type="number" id="total_amount" class="form-control" step="0.01" readonly>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/procurement" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Create Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate totals automatically
document.getElementById('subtotal').addEventListener('input', calculateTotals);
document.getElementById('tax_rate').addEventListener('input', calculateTotals);

function calculateTotals() {
    const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;

    const taxAmount = (subtotal * taxRate) / 100;
    const totalAmount = subtotal + taxAmount;

    document.getElementById('tax_amount').value = taxAmount.toFixed(2);
    document.getElementById('total_amount').value = totalAmount.toFixed(2);
}
</script>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
