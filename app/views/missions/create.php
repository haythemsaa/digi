<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-plus"></i> Nouvelle Mission</h1>
            <p class="text-muted">Créer une nouvelle mission ou livraison</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/missions" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/missions/create">
        <div class="row">
            <div class="col-md-8">
                <!-- Mission Details -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle"></i> Informations de Mission
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de Mission *</label>
                                    <select class="form-control" name="mission_type" required>
                                        <option value="delivery">Livraison</option>
                                        <option value="pickup">Ramassage</option>
                                        <option value="transport">Transport</option>
                                        <option value="service">Service</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="other">Autre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Priorité</label>
                                    <select class="form-control" name="priority">
                                        <option value="low">Basse</option>
                                        <option value="normal" selected>Normale</option>
                                        <option value="high">Haute</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3"><i class="fas fa-user"></i> Informations Client</h6>
                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom du Client *</label>
                                    <input type="text" class="form-control" name="client_name"
                                           placeholder="Société ou nom complet" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Téléphone</label>
                                    <input type="tel" class="form-control" name="client_phone"
                                           placeholder="+216 XX XXX XXX">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="client_email"
                                           placeholder="client@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Adresse</label>
                                    <input type="text" class="form-control" name="client_address"
                                           placeholder="Adresse complète">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3"
                                      placeholder="Détails de la mission..."></textarea>
                        </div>

                        <h6 class="mt-3"><i class="fas fa-map-marker-alt"></i> Lieux</h6>
                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lieu de Départ/Ramassage</label>
                                    <input type="text" class="form-control" name="pickup_location"
                                           placeholder="Adresse de départ">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lieu de Livraison/Arrivée</label>
                                    <input type="text" class="form-control" name="delivery_location"
                                           placeholder="Adresse de livraison">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Distance Estimée (km)</label>
                                    <input type="number" step="0.1" class="form-control" name="estimated_distance_km"
                                           placeholder="50">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Durée Estimée (minutes)</label>
                                    <input type="number" class="form-control" name="estimated_duration_minutes"
                                           placeholder="60">
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3"><i class="fas fa-calendar"></i> Planification</h6>
                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Début Planifié</label>
                                    <input type="datetime-local" class="form-control" name="scheduled_start">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fin Planifiée</label>
                                    <input type="datetime-local" class="form-control" name="scheduled_end">
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3"><i class="fas fa-car"></i> Affectation</h6>
                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Véhicule</label>
                                    <select class="form-control" name="assigned_vehicle_id">
                                        <option value="">Non assigné</option>
                                        <?php foreach ($data['vehicles'] as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>">
                                                <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['brand'] . ' ' . $vehicle['model']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Conducteur</label>
                                    <select class="form-control" name="assigned_driver_id">
                                        <option value="">Non assigné</option>
                                        <!-- Will be populated with drivers -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" rows="3"
                                      placeholder="Notes internes, instructions spéciales..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Mission Items -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-boxes"></i> Articles à Transporter (Optionnel)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            <div class="item-row row mb-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="items[0][item_name]"
                                           placeholder="Nom de l'article">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="items[0][item_description]"
                                           placeholder="Description">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="items[0][quantity]"
                                           placeholder="Quantité" value="1">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.1" class="form-control" name="items[0][weight_kg]"
                                           placeholder="Poids (kg)">
                                </div>
                                <div class="col-md-1">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="fragile0"
                                               name="items[0][fragile]" value="1">
                                        <label class="custom-control-label" for="fragile0" title="Fragile">
                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" id="addItemBtn">
                            <i class="fas fa-plus"></i> Ajouter Article
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> Aide</h6>
                    </div>
                    <div class="card-body">
                        <h6>Types de Missions</h6>
                        <ul class="small">
                            <li><strong>Livraison:</strong> Transport de marchandises vers client</li>
                            <li><strong>Ramassage:</strong> Collecte de marchandises</li>
                            <li><strong>Transport:</strong> Déplacement de personnes ou biens</li>
                            <li><strong>Service:</strong> Intervention technique</li>
                        </ul>

                        <h6 class="mt-3">Niveaux de Priorité</h6>
                        <ul class="small">
                            <li><span class="badge badge-secondary">Basse</span> - Pas urgent</li>
                            <li><span class="badge badge-info">Normale</span> - Standard</li>
                            <li><span class="badge badge-warning">Haute</span> - Important</li>
                            <li><span class="badge badge-danger">Urgente</span> - Immédiat</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Créer la Mission
                        </button>
                        <a href="<?php echo APP_URL; ?>/missions" class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let itemIndex = 1;

$('#addItemBtn').click(function() {
    const newItem = `
        <div class="item-row row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="items[${itemIndex}][item_name]"
                       placeholder="Nom de l'article">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="items[${itemIndex}][item_description]"
                       placeholder="Description">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="items[${itemIndex}][quantity]"
                       placeholder="Quantité" value="1">
            </div>
            <div class="col-md-2">
                <input type="number" step="0.1" class="form-control" name="items[${itemIndex}][weight_kg]"
                       placeholder="Poids (kg)">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    $('#itemsContainer').append(newItem);
    itemIndex++;
});

$(document).on('click', '.remove-item', function() {
    $(this).closest('.item-row').remove();
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
