<?php
$data['title'] = 'Edit Part';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-edit me-2"></i>Edit Part: <?php echo $data['part']['name']; ?></h4>
                <a href="<?php echo APP_URL; ?>/inventory" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Inventory
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/inventory/editPart/<?php echo $data['part']['id']; ?>">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Part Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Part Number <span class="text-danger">*</span></label>
                                <input type="text" name="part_number" class="form-control" value="<?php echo $data['part']['part_number']; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Part Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?php echo $data['part']['name']; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-control">
                                    <option value="engine" <?php echo $data['part']['category'] === 'engine' ? 'selected' : ''; ?>>Engine Parts</option>
                                    <option value="transmission" <?php echo $data['part']['category'] === 'transmission' ? 'selected' : ''; ?>>Transmission</option>
                                    <option value="brakes" <?php echo $data['part']['category'] === 'brakes' ? 'selected' : ''; ?>>Brakes</option>
                                    <option value="electrical" <?php echo $data['part']['category'] === 'electrical' ? 'selected' : ''; ?>>Electrical</option>
                                    <option value="filters" <?php echo $data['part']['category'] === 'filters' ? 'selected' : ''; ?>>Filters</option>
                                    <option value="oils" <?php echo $data['part']['category'] === 'oils' ? 'selected' : ''; ?>>Oils & Fluids</option>
                                    <option value="tires" <?php echo $data['part']['category'] === 'tires' ? 'selected' : ''; ?>>Tires</option>
                                    <option value="body" <?php echo $data['part']['category'] === 'body' ? 'selected' : ''; ?>>Body Parts</option>
                                    <option value="other" <?php echo $data['part']['category'] === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Manufacturer</label>
                                <input type="text" name="manufacturer" class="form-control" value="<?php echo $data['part']['manufacturer'] ?? ''; ?>">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo $data['part']['description'] ?? ''; ?></textarea>
                            </div>

                            <div class="col-md-12 mt-3">
                                <h5 class="border-bottom pb-2 mb-3">Stock & Pricing</h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit Price (TND) <span class="text-danger">*</span></label>
                                <input type="number" name="unit_price" class="form-control" step="0.01" value="<?php echo $data['part']['unit_price']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantity in Stock</label>
                                <input type="number" name="quantity_in_stock" class="form-control" value="<?php echo $data['part']['quantity_in_stock']; ?>" readonly>
                                <small class="text-muted">Use stock movements to update quantity</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" name="reorder_level" class="form-control" value="<?php echo $data['part']['reorder_level']; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" value="<?php echo $data['part']['location'] ?? ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Supplier</label>
                                <input type="text" name="supplier" class="form-control" value="<?php echo $data['part']['supplier'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="<?php echo APP_URL; ?>/inventory" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Part
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
