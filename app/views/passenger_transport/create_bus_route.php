<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-plus"></i> Créer une Ligne de Bus</h1>
            <p class="text-muted">Définir un nouvel itinéraire de bus</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/busRoutes" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux Lignes
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/passenger_transport/createBusRoute">
        <div class="row">
            <!-- Left Column: Route Details -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> Informations de la Ligne</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="route_number">Numéro de Ligne <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="route_number" name="route_number"
                                           placeholder="Ex: 1, 12, A1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color_code">Couleur</label>
                                    <input type="color" class="form-control" id="color_code" name="color_code"
                                           value="#007bff">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="route_name">Nom de la Ligne <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="route_name" name="route_name"
                                   placeholder="Ex: Centre-Ville - Aéroport" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"
                                      placeholder="Description de l'itinéraire..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="route_type">Type de Ligne <span class="text-danger">*</span></label>
                            <select class="form-control" id="route_type" name="route_type" required>
                                <option value="urban" selected>Urbain</option>
                                <option value="suburban">Banlieue</option>
                                <option value="intercity">Intercité</option>
                                <option value="express">Express</option>
                                <option value="shuttle">Navette</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_point">Point de Départ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="start_point" name="start_point"
                                   placeholder="Ex: Gare Centrale" required>
                        </div>

                        <div class="form-group">
                            <label for="end_point">Point d'Arrivée <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="end_point" name="end_point"
                                   placeholder="Ex: Aéroport Carthage" required>
                        </div>

                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="is_circular" name="is_circular" value="1">
                            <label class="custom-control-label" for="is_circular">
                                <i class="fas fa-sync-alt text-info"></i>
                                Ligne Circulaire (retour au point de départ)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Schedule & Pricing -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-clock"></i> Horaires et Fréquence</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_departure">Premier Départ</label>
                                    <input type="time" class="form-control" id="first_departure" name="first_departure"
                                           value="06:00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_departure">Dernier Départ</label>
                                    <input type="time" class="form-control" id="last_departure" name="last_departure"
                                           value="22:00">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="frequency_minutes">Fréquence (minutes)</label>
                            <input type="number" class="form-control" id="frequency_minutes" name="frequency_minutes"
                                   min="5" max="120" value="30" placeholder="30">
                            <small class="form-text text-muted">Temps moyen entre deux bus</small>
                        </div>

                        <h6 class="mt-3"><i class="fas fa-calendar"></i> Jours de Service</h6>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="operates_weekdays" name="operates_weekdays" value="1" checked>
                            <label class="custom-control-label" for="operates_weekdays">
                                Lundi - Vendredi
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="operates_weekends" name="operates_weekends" value="1" checked>
                            <label class="custom-control-label" for="operates_weekends">
                                Samedi - Dimanche
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="operates_holidays" name="operates_holidays" value="1">
                            <label class="custom-control-label" for="operates_holidays">
                                Jours Fériés
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="m-0"><i class="fas fa-money-bill-wave"></i> Tarification</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="base_fare">Tarif de Base (TND)</label>
                                    <input type="number" step="0.01" class="form-control" id="base_fare" name="base_fare"
                                           placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fare_per_km">Prix par km (TND)</label>
                                    <input type="number" step="0.01" class="form-control" id="fare_per_km" name="fare_per_km"
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0"><i class="fas fa-route"></i> Caractéristiques du Trajet</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_distance_km">Distance Totale (km)</label>
                                    <input type="number" step="0.1" class="form-control" id="total_distance_km" name="total_distance_km"
                                           placeholder="0.0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estimated_duration_minutes">Durée Estimée (min)</label>
                                    <input type="number" class="form-control" id="estimated_duration_minutes" name="estimated_duration_minutes"
                                           placeholder="0">
                                </div>
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
                            <i class="fas fa-save"></i> Créer la Ligne
                        </button>
                        <a href="<?php echo APP_URL; ?>/passenger_transport/busRoutes" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <div class="float-right">
                            <span class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Vous pourrez ajouter les arrêts après la création
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
