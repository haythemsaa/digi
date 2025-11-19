<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit"></i> Éditer la Route: <?php echo htmlspecialchars($data['route']['route_number']); ?>
            </h1>
            <p class="text-muted">
                Ajouter des colis et gérer les arrêts de livraison
            </p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/smart_delivery/viewRoute/<?php echo $data['route']['id']; ?>"
               class="btn btn-info">
                <i class="fas fa-eye"></i> Voir Détails
            </a>
            <a href="<?php echo APP_URL; ?>/smart_delivery/routes" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Route Summary -->
        <div class="col-md-12">
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <h6 class="text-muted">Véhicule</h6>
                            <strong><?php echo htmlspecialchars($data['route']['registration_number']); ?></strong>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted">Date</h6>
                            <strong><?php echo date('d/m/Y', strtotime($data['route']['route_date'])); ?></strong>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted">Colis</h6>
                            <strong><?php echo count($data['route']['stops'] ?? []); ?> colis</strong>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted">Distance</h6>
                            <strong>
                                <?php echo $data['route']['total_distance'] ? number_format($data['route']['total_distance'], 1) . ' km' : 'N/A'; ?>
                            </strong>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted">Statut</h6>
                            <?php
                            $statusClasses = [
                                'draft' => 'secondary',
                                'optimized' => 'info',
                                'assigned' => 'primary',
                                'loading' => 'warning',
                                'in_progress' => 'warning',
                                'completed' => 'success'
                            ];
                            $badgeClass = $statusClasses[$data['route']['status']] ?? 'secondary';
                            ?>
                            <span class="badge badge-<?php echo $badgeClass; ?> p-2">
                                <?php echo ucfirst($data['route']['status']); ?>
                            </span>
                        </div>
                        <div class="col-md-2">
                            <?php if ($data['route']['status'] == 'draft'): ?>
                                <a href="<?php echo APP_URL; ?>/smart_delivery/optimizeRoute/<?php echo $data['route']['id']; ?>"
                                   class="btn btn-sm btn-primary btn-block">
                                    <i class="fas fa-brain"></i> Optimiser
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Add Package Form -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-plus"></i> Ajouter un Colis</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/smart_delivery/editRoute/<?php echo $data['route']['id']; ?>">
                        <input type="hidden" name="add_package" value="1">

                        <div class="form-group">
                            <label for="package_id">Colis Disponibles</label>
                            <select class="form-control" id="package_id" name="package_id" required>
                                <option value="">Sélectionner un colis</option>
                                <?php foreach ($data['pending_packages'] as $pkg): ?>
                                    <option value="<?php echo $pkg['id']; ?>"
                                            data-weight="<?php echo $pkg['weight']; ?>"
                                            data-address="<?php echo htmlspecialchars($pkg['delivery_address']); ?>">
                                        <?php echo htmlspecialchars($pkg['package_number']); ?>
                                        - <?php echo $pkg['weight']; ?> kg
                                        <?php if ($pkg['priority'] == 'urgent'): ?>
                                            <span class="text-danger">(URGENT)</span>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                <?php echo count($data['pending_packages']); ?> colis en attente
                            </small>
                        </div>

                        <div id="package-preview" class="alert alert-info" style="display: none;">
                            <h6>Aperçu du Colis</h6>
                            <p class="mb-0">
                                <strong>Poids:</strong> <span id="preview-weight"></span><br>
                                <strong>Adresse:</strong> <span id="preview-address"></span>
                            </p>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-plus"></i> Ajouter à la Route
                        </button>
                    </form>

                    <?php if (empty($data['pending_packages'])): ?>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucun colis en attente disponible.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4 border-left-info">
                <div class="card-body">
                    <h6><i class="fas fa-chart-bar text-info"></i> Statistiques</h6>
                    <?php
                    $totalWeight = 0;
                    $totalVolume = 0;
                    foreach ($data['route']['stops'] ?? [] as $stop) {
                        $totalWeight += floatval($stop['weight'] ?? 0);
                        $totalVolume += floatval($stop['volume'] ?? 0);
                    }
                    ?>
                    <p class="mb-1">
                        <strong>Poids Total:</strong> <?php echo number_format($totalWeight, 2); ?> kg
                    </p>
                    <p class="mb-1">
                        <strong>Volume Total:</strong> <?php echo number_format($totalVolume, 3); ?> m³
                    </p>
                    <p class="mb-0">
                        <strong>Nombre d'arrêts:</strong> <?php echo count($data['route']['stops'] ?? []); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Current Stops List -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0">
                        <i class="fas fa-list"></i> Colis sur cette Route
                        (<?php echo count($data['route']['stops'] ?? []); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['route']['stops'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Numéro Colis</th>
                                        <th>Adresse de Livraison</th>
                                        <th>Contact</th>
                                        <th>Poids</th>
                                        <th>Priorité</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="stops-sortable">
                                    <?php foreach ($data['route']['stops'] as $stop): ?>
                                        <tr data-stop-id="<?php echo $stop['id']; ?>">
                                            <td class="text-center">
                                                <span class="badge badge-secondary">
                                                    <?php echo $stop['stop_sequence']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($stop['package_number']); ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-danger"></i>
                                                <?php echo htmlspecialchars($stop['address']); ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($stop['delivery_contact'] ?? 'N/A'); ?><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone"></i>
                                                    <?php echo htmlspecialchars($stop['delivery_phone'] ?? 'N/A'); ?>
                                                </small>
                                            </td>
                                            <td><?php echo $stop['weight']; ?> kg</td>
                                            <td>
                                                <?php
                                                $priorityClasses = [
                                                    'low' => 'secondary',
                                                    'normal' => 'info',
                                                    'high' => 'warning',
                                                    'urgent' => 'danger'
                                                ];
                                                $priorityClass = $priorityClasses[$stop['priority'] ?? 'normal'] ?? 'info';
                                                ?>
                                                <span class="badge badge-<?php echo $priorityClass; ?>">
                                                    <?php echo ucfirst($stop['priority'] ?? 'normal'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/smart_delivery/removeFromRoute/<?php echo $stop['id']; ?>"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Retirer ce colis de la route?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>Astuce:</strong> Une fois tous les colis ajoutés, cliquez sur "Optimiser"
                            pour que l'IA calcule le meilleur ordre de livraison et le plan de chargement optimal.
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun colis sur cette route</h5>
                            <p>Commencez par ajouter des colis en utilisant le formulaire à gauche.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Map Preview (Placeholder) -->
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-map"></i> Aperçu Carte</h6>
                </div>
                <div class="card-body">
                    <div id="route-map" style="height: 300px; background: #e9ecef; border-radius: 4px;">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <i class="fas fa-map-marked-alt fa-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">
                                    La carte interactive sera affichée ici après optimisation
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Package preview on selection
document.getElementById('package_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const preview = document.getElementById('package-preview');

    if (this.value) {
        document.getElementById('preview-weight').textContent = selected.dataset.weight + ' kg';
        document.getElementById('preview-address').textContent = selected.dataset.address;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
