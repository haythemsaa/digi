<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-boxes"></i>
                <?php echo $data['pack'] ? 'Modifier le Pack' : 'Nouveau Pack'; ?>
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/subscription-manager/packs" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux Packs
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/subscription-manager/createPack/<?php echo $data['pack']['id'] ?? ''; ?>">
        <div class="row">
            <!-- Left Column: Basic Info -->
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> Informations du Pack</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pack_code">Code du Pack <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="pack_code" name="pack_code"
                                           value="<?php echo $data['pack']['pack_code'] ?? ''; ?>"
                                           <?php echo $data['pack'] ? 'readonly' : 'required'; ?>
                                           placeholder="taxi_pack, delivery_pack...">
                                    <small class="form-text text-muted">Code unique (lettres minuscules, underscores)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pack_name">Nom du Pack <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="pack_name" name="pack_name"
                                           value="<?php echo $data['pack']['pack_name'] ?? ''; ?>" required
                                           placeholder="Pack Taxi">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Solution complète pour..."><?php echo $data['pack']['description'] ?? ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price_monthly">Prix Mensuel (€) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly"
                                           value="<?php echo $data['pack']['price_monthly'] ?? '0.00'; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price_yearly">Prix Annuel (€)</label>
                                    <input type="number" step="0.01" class="form-control" id="price_yearly" name="price_yearly"
                                           value="<?php echo $data['pack']['price_yearly'] ?? '0.00'; ?>">
                                    <small class="form-text text-muted">Laissez vide pour 10x le mensuel</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="discount_percent">Réduction (%)</label>
                                    <input type="number" step="0.01" class="form-control" id="discount_percent" name="discount_percent"
                                           value="<?php echo $data['pack']['discount_percent'] ?? '0'; ?>">
                                    <small class="form-text text-muted">vs prix total des modules</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Modules Inclus <span class="text-danger">*</span></label>
                            <div class="row">
                                <?php
                                $selectedModules = [];
                                if ($data['pack'] && !empty($data['pack']['modules'])) {
                                    foreach ($data['pack']['modules'] as $module) {
                                        $selectedModules[] = $module['id'];
                                    }
                                }

                                foreach ($data['all_modules'] as $module):
                                    $isChecked = in_array($module['id'], $selectedModules);
                                ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input module-checkbox"
                                                   id="module_<?php echo $module['id']; ?>"
                                                   name="modules[]"
                                                   value="<?php echo $module['id']; ?>"
                                                   data-price="<?php echo $module['price_monthly']; ?>"
                                                   <?php echo $isChecked ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="module_<?php echo $module['id']; ?>">
                                                <i class="fas <?php echo $module['icon']; ?> mr-2" style="color: <?php echo $module['color']; ?>"></i>
                                                <strong><?php echo htmlspecialchars($module['module_name']); ?></strong>
                                                <span class="text-muted">(<?php echo number_format($module['price_monthly'], 2); ?>€/mois)</span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="alert alert-info mt-2" id="modules-price-info">
                                <strong>Prix total des modules sélectionnés:</strong> <span id="total-modules-price">0.00</span>€/mois
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & Preview -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-cog"></i> Paramètres</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="sort_order">Ordre d'Affichage</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order"
                                   value="<?php echo $data['pack']['sort_order'] ?? '0'; ?>">
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured"
                                       <?php echo ($data['pack']['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_featured">Pack Recommandé</label>
                            </div>
                            <small class="form-text text-muted">Affiche un badge "RECOMMANDÉ"</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                       <?php echo ($data['pack']['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_active">Pack Actif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="card shadow" id="pack-preview">
                    <div class="card-header bg-primary text-white text-center" id="preview-featured" style="display: none;">
                        <i class="fas fa-star"></i> <strong>RECOMMANDÉ</strong>
                    </div>
                    <div class="card-body text-center">
                        <h6 class="text-muted">Aperçu</h6>
                        <h4 id="preview-name"><?php echo $data['pack']['pack_name'] ?? 'Nom du Pack'; ?></h4>
                        <p class="text-muted small" id="preview-description">
                            <?php echo $data['pack']['description'] ?? 'Description'; ?>
                        </p>
                        <h2 class="text-primary" id="preview-price">
                            <?php echo number_format($data['pack']['price_monthly'] ?? 0, 2); ?>€
                        </h2>
                        <small class="text-muted">par mois</small>
                        <div class="mt-2" id="preview-discount" style="display: none;">
                            <span class="badge badge-success">
                                <i class="fas fa-tag"></i> Économie: <span id="preview-discount-value">0</span>%
                            </span>
                        </div>
                        <div id="preview-modules" class="mt-3">
                            <small class="text-muted">Modules inclus: <span id="preview-modules-count">0</span></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> <?php echo $data['pack'] ? 'Mettre à Jour' : 'Créer le Pack'; ?>
                        </button>
                        <a href="<?php echo APP_URL; ?>/subscription-manager/packs" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-calculate yearly price
document.getElementById('price_monthly').addEventListener('input', function() {
    var monthly = parseFloat(this.value) || 0;
    var yearlyInput = document.getElementById('price_yearly');
    if (!yearlyInput.value || yearlyInput.value == 0) {
        yearlyInput.value = (monthly * 10).toFixed(2);
    }
    updatePreview();
});

// Calculate total modules price
function calculateTotalModulesPrice() {
    var total = 0;
    document.querySelectorAll('.module-checkbox:checked').forEach(function(checkbox) {
        total += parseFloat(checkbox.dataset.price);
    });
    document.getElementById('total-modules-price').textContent = total.toFixed(2);
    document.getElementById('preview-modules-count').textContent = document.querySelectorAll('.module-checkbox:checked').length;
    return total;
}

// Update preview
function updatePreview() {
    document.getElementById('preview-name').textContent = document.getElementById('pack_name').value || 'Nom du Pack';
    document.getElementById('preview-description').textContent = document.getElementById('description').value || 'Description';

    var price = parseFloat(document.getElementById('price_monthly').value) || 0;
    document.getElementById('preview-price').textContent = price.toFixed(2) + '€';

    var discount = parseFloat(document.getElementById('discount_percent').value) || 0;
    if (discount > 0) {
        document.getElementById('preview-discount').style.display = 'block';
        document.getElementById('preview-discount-value').textContent = discount.toFixed(0);
    } else {
        document.getElementById('preview-discount').style.display = 'none';
    }

    if (document.getElementById('is_featured').checked) {
        document.getElementById('preview-featured').style.display = 'block';
    } else {
        document.getElementById('preview-featured').style.display = 'none';
    }
}

// Update on module selection
document.querySelectorAll('.module-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', calculateTotalModulesPrice);
});

// Update preview on input
document.getElementById('pack_name').addEventListener('input', updatePreview);
document.getElementById('description').addEventListener('input', updatePreview);
document.getElementById('price_monthly').addEventListener('input', updatePreview);
document.getElementById('discount_percent').addEventListener('input', updatePreview);
document.getElementById('is_featured').addEventListener('change', updatePreview);

// Initial calculation
calculateTotalModulesPrice();
updatePreview();
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
