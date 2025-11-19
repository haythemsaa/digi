<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0"><i class="fas fa-route"></i> Créer une Nouvelle Route</h1>
            <p class="text-muted">Créez une nouvelle route de livraison optimisée par IA</p>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/smart_delivery/createRoute">
        <div class="row">
            <!-- Left Column: Basic Info -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> Informations de Base</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="route_date">Date de Livraison <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="route_date" name="route_date"
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="vehicle_id">Véhicule <span class="text-danger">*</span></label>
                            <select class="form-control" id="vehicle_id" name="vehicle_id" required>
                                <option value="">Sélectionner un véhicule</option>
                                <?php foreach ($data['vehicles'] as $vehicle): ?>
                                    <option value="<?php echo $vehicle['id']; ?>"
                                            data-capacity-weight="<?php echo $vehicle['payload_capacity']; ?>"
                                            data-capacity-volume="<?php echo $vehicle['cargo_volume'] ?? 0; ?>">
                                        <?php echo htmlspecialchars($vehicle['registration_number']); ?>
                                        - <?php echo htmlspecialchars($vehicle['type']); ?>
                                        (<?php echo $vehicle['payload_capacity']; ?> kg)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="driver_id">Chauffeur</label>
                            <select class="form-control" id="driver_id" name="driver_id">
                                <option value="">Assigner plus tard</option>
                                <?php foreach ($data['drivers'] as $driver): ?>
                                    <option value="<?php echo $driver['id']; ?>">
                                        <?php echo htmlspecialchars($driver['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_location">Point de Départ</label>
                            <input type="text" class="form-control" id="start_location" name="start_location"
                                   value="Dépôt Principal" placeholder="Adresse de départ">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_lat">Latitude</label>
                                    <input type="number" step="0.000001" class="form-control" id="start_lat"
                                           name="start_lat" value="36.8065" placeholder="36.8065">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_lng">Longitude</label>
                                    <input type="number" step="0.000001" class="form-control" id="start_lng"
                                           name="start_lng" value="10.1815" placeholder="10.1815">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Options -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-cog"></i> Options d'Optimisation</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Type d'Optimisation</label>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="optimize_route"
                                       name="optimize_route" value="1" checked>
                                <label class="custom-control-label" for="optimize_route">
                                    <i class="fas fa-route text-primary"></i>
                                    <strong>Optimiser le trajet</strong> (algorithme génétique VRP)
                                </label>
                            </div>

                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="optimize_loading"
                                       name="optimize_loading" value="1" checked>
                                <label class="custom-control-label" for="optimize_loading">
                                    <i class="fas fa-cube text-success"></i>
                                    <strong>Optimiser le chargement</strong> (Bin Packing 3D)
                                </label>
                            </div>

                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="respect_time_windows"
                                       name="respect_time_windows" value="1" checked>
                                <label class="custom-control-label" for="respect_time_windows">
                                    <i class="fas fa-clock text-warning"></i>
                                    <strong>Respecter les créneaux horaires</strong>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="max_stops">Nombre Maximum d'Arrêts</label>
                            <input type="number" class="form-control" id="max_stops" name="max_stops"
                                   min="1" max="50" value="20">
                            <small class="form-text text-muted">Limite le nombre de livraisons par route</small>
                        </div>

                        <div class="form-group">
                            <label for="max_duration">Durée Maximale (minutes)</label>
                            <input type="number" class="form-control" id="max_duration" name="max_duration"
                                   min="30" max="720" value="480" step="30">
                            <small class="form-text text-muted">Durée maximale de la tournée (8h par défaut)</small>
                        </div>

                        <div class="form-group">
                            <label for="priority">Priorité de la Route</label>
                            <select class="form-control" id="priority" name="priority">
                                <option value="low">Basse</option>
                                <option value="normal" selected>Normale</option>
                                <option value="high">Haute</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4 border-left-info">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle text-info"></i> Information</h6>
                        <p class="mb-0">
                            Après la création, vous pourrez ajouter des colis à cette route et lancer
                            l'optimisation intelligente par IA.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="notes">Notes / Instructions Spéciales</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Instructions spéciales pour cette route..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Créer la Route
                        </button>
                        <a href="<?php echo APP_URL; ?>/smart_delivery/routes" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Show vehicle capacity when selected
document.getElementById('vehicle_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const weight = selected.dataset.capacityWeight;
    const volume = selected.dataset.capacityVolume;

    console.log('Vehicle capacity:', weight, 'kg,', volume, 'm³');
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
