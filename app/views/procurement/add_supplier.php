<?php
$data['title'] = 'Add Supplier';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-building me-2"></i>Add New Supplier</h4>
                <a href="<?php echo APP_URL; ?>/procurement" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Procurement
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/procurement/addSupplier">
                        <div class="row">
                            <!-- Company Information -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Company Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax ID / VAT Number</label>
                                <input type="text" name="tax_id" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="parts">Parts & Accessories</option>
                                    <option value="fuel">Fuel Supplier</option>
                                    <option value="maintenance">Maintenance Services</option>
                                    <option value="tires">Tires</option>
                                    <option value="insurance">Insurance</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Contact Information</h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mobile</label>
                                <input type="text" name="mobile" class="form-control">
                            </div>

                            <!-- Address Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Address</h5>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Street Address</label>
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

                            <!-- Payment & Status -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Payment & Status</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Terms (Days)</label>
                                <input type="number" name="payment_terms" class="form-control" value="30">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/procurement" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
