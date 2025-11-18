<?php
$data['title'] = 'Add Client';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-user-plus me-2"></i>Add New Client</h4>
                <a href="<?php echo APP_URL; ?>/transport/clients" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Clients
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/transport/addClient">
                        <div class="row">
                            <!-- Client Type -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Client Type <span class="text-danger">*</span></label>
                                <select name="client_type" id="clientType" class="form-control" required>
                                    <option value="company">Company</option>
                                    <option value="individual">Individual</option>
                                </select>
                            </div>

                            <!-- Company Information -->
                            <div class="col-md-12" id="companyFields">
                                <h5 class="border-bottom pb-2 mb-3">Company Information</h5>
                            </div>

                            <div class="col-md-6 mb-3" id="companyNameField">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax ID</label>
                                <input type="text" name="tax_id" class="form-control">
                            </div>

                            <!-- Individual Information -->
                            <div class="col-md-12" id="individualFields" style="display:none;">
                                <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                            </div>

                            <div class="col-md-6 mb-3" id="firstNameField" style="display:none;">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3" id="lastNameField" style="display:none;">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control">
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Contact Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile</label>
                                <input type="text" name="mobile" class="form-control">
                            </div>

                            <!-- Address -->
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

                            <!-- Additional Information -->
                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Additional Information</h5>
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
                            <a href="<?php echo APP_URL; ?>/transport/clients" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Client
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('clientType').addEventListener('change', function() {
    const isCompany = this.value === 'company';

    document.getElementById('companyFields').style.display = isCompany ? 'block' : 'none';
    document.getElementById('companyNameField').style.display = isCompany ? 'block' : 'none';
    document.getElementById('individualFields').style.display = isCompany ? 'none' : 'block';
    document.getElementById('firstNameField').style.display = isCompany ? 'none' : 'block';
    document.getElementById('lastNameField').style.display = isCompany ? 'none' : 'block';

    // Update required fields
    document.querySelector('input[name="company_name"]').required = isCompany;
    document.querySelector('input[name="first_name"]').required = !isCompany;
    document.querySelector('input[name="last_name"]').required = !isCompany;
});
</script>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
