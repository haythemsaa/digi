<?php
$data['title'] = 'Add Part';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-plus-circle me-2"></i>Add New Part</h4>
                <a href="<?php echo APP_URL; ?>/inventory" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Inventory
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/inventory/addPart">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Part Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Part Number <span class="text-danger">*</span></label>
                                <input type="text" name="part_number" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Part Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-control">
                                    <option value="engine">Engine Parts</option>
                                    <option value="transmission">Transmission</option>
                                    <option value="brakes">Brakes</option>
                                    <option value="electrical">Electrical</option>
                                    <option value="filters">Filters</option>
                                    <option value="oils">Oils & Fluids</option>
                                    <option value="tires">Tires</option>
                                    <option value="body">Body Parts</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Manufacturer</label>
                                <input type="text" name="manufacturer" class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Stock & Pricing</h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit Price (TND) <span class="text-danger">*</span></label>
                                <input type="number" name="unit_price" class="form-control" step="0.01" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantity in Stock <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_in_stock" class="form-control" value="0" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" name="reorder_level" class="form-control" value="10">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g., Shelf A1">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Supplier</label>
                                <input type="text" name="supplier" class="form-control">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/inventory" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Part
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
