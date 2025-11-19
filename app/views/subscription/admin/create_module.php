<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-puzzle-piece"></i>
                <?php echo $data['module'] ? 'Modifier le Module' : 'Nouveau Module'; ?>
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/subscription-manager/modules" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux Modules
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/subscription-manager/createModule/<?php echo $data['module']['id'] ?? ''; ?>">
        <div class="row">
            <!-- Left Column: Basic Info -->
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> Informations du Module</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="module_code">Code du Module <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="module_code" name="module_code"
                                           value="<?php echo $data['module']['module_code'] ?? ''; ?>"
                                           <?php echo $data['module'] ? 'readonly' : 'required'; ?>
                                           placeholder="gps, taxi, delivery...">
                                    <small class="form-text text-muted">Code unique (lettres minuscules, underscores uniquement)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="module_name">Nom du Module <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="module_name" name="module_name"
                                           value="<?php echo $data['module']['module_name'] ?? ''; ?>" required
                                           placeholder="GPS & Tracking">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Description courte du module..."><?php echo $data['module']['description'] ?? ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price_monthly">Prix Mensuel (€) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly"
                                           value="<?php echo $data['module']['price_monthly'] ?? '0.00'; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price_yearly">Prix Annuel (€)</label>
                                    <input type="number" step="0.01" class="form-control" id="price_yearly" name="price_yearly"
                                           value="<?php echo $data['module']['price_yearly'] ?? '0.00'; ?>">
                                    <small class="form-text text-muted">Laissez vide pour 10x le prix mensuel</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Fonctionnalités Incluses</label>
                            <div id="features-container">
                                <?php
                                $features = $data['module'] ? json_decode($data['module']['features'], true) : [];
                                if (empty($features)) {
                                    $features = [''];
                                }
                                foreach ($features as $index => $feature):
                                ?>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="features[]"
                                               value="<?php echo htmlspecialchars($feature); ?>"
                                               placeholder="Ex: Suivi en temps réel">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-danger" onclick="removeFeature(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" onclick="addFeature()">
                                <i class="fas fa-plus"></i> Ajouter une fonctionnalité
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Display & Settings -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-palette"></i> Affichage</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="icon">Icône FontAwesome</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas <?php echo $data['module']['icon'] ?? 'fa-star'; ?>" id="icon-preview"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="icon" name="icon"
                                       value="<?php echo $data['module']['icon'] ?? 'fa-star'; ?>"
                                       placeholder="fa-star">
                            </div>
                            <small class="form-text text-muted">
                                <a href="https://fontawesome.com/icons" target="_blank">Voir les icônes</a>
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="color">Couleur</label>
                            <input type="color" class="form-control" id="color" name="color"
                                   value="<?php echo $data['module']['color'] ?? '#007bff'; ?>">
                        </div>

                        <div class="form-group">
                            <label for="sort_order">Ordre d'Affichage</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order"
                                   value="<?php echo $data['module']['sort_order'] ?? '0'; ?>">
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                       <?php echo ($data['module']['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_active">Module Actif</label>
                            </div>
                        </div>

                        <!-- Preview Card -->
                        <div class="card bg-light mt-4">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Aperçu</h6>
                                <i class="fas <?php echo $data['module']['icon'] ?? 'fa-star'; ?> fa-3x mb-2"
                                   id="preview-icon"
                                   style="color: <?php echo $data['module']['color'] ?? '#007bff'; ?>"></i>
                                <h5 id="preview-name"><?php echo $data['module']['module_name'] ?? 'Nom du Module'; ?></h5>
                                <p class="text-muted small" id="preview-description">
                                    <?php echo $data['module']['description'] ?? 'Description'; ?>
                                </p>
                                <h4 class="text-primary" id="preview-price">
                                    <?php echo number_format($data['module']['price_monthly'] ?? 0, 2); ?>€/mois
                                </h4>
                            </div>
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
                            <i class="fas fa-save"></i> <?php echo $data['module'] ? 'Mettre à Jour' : 'Créer le Module'; ?>
                        </button>
                        <a href="<?php echo APP_URL; ?>/subscription-manager/modules" class="btn btn-secondary btn-lg">
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

// Update icon preview
document.getElementById('icon').addEventListener('input', function() {
    var iconClass = this.value;
    document.getElementById('icon-preview').className = 'fas ' + iconClass;
    document.getElementById('preview-icon').className = 'fas ' + iconClass + ' fa-3x mb-2';
    updatePreview();
});

// Update color preview
document.getElementById('color').addEventListener('input', function() {
    document.getElementById('preview-icon').style.color = this.value;
});

// Update name preview
document.getElementById('module_name').addEventListener('input', function() {
    document.getElementById('preview-name').textContent = this.value || 'Nom du Module';
});

// Update description preview
document.getElementById('description').addEventListener('input', function() {
    document.getElementById('preview-description').textContent = this.value || 'Description';
});

// Update price preview
document.getElementById('price_monthly').addEventListener('input', function() {
    var price = parseFloat(this.value) || 0;
    document.getElementById('preview-price').textContent = price.toFixed(2) + '€/mois';
});

function addFeature() {
    var container = document.getElementById('features-container');
    var newFeature = `
        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-check"></i></span>
            </div>
            <input type="text" class="form-control" name="features[]" placeholder="Ex: Suivi en temps réel">
            <div class="input-group-append">
                <button type="button" class="btn btn-danger" onclick="removeFeature(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newFeature);
}

function removeFeature(btn) {
    btn.closest('.input-group').remove();
}

function updatePreview() {
    // Additional preview updates
}
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
