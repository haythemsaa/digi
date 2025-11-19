<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0"><i class="fas fa-plus"></i> Nouveau Colis</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/smart_delivery">Smart Delivery</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/smart_delivery/packages">Colis</a></li>
                    <li class="breadcrumb-item active">Nouveau</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/smart_delivery/addPackage">
                        <div class="row">
                            <!-- Client Information -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3"><i class="fas fa-user"></i> Informations Client</h5>

                                <div class="form-group">
                                    <label>Client *</label>
                                    <select name="client_id" class="form-control" required>
                                        <option value="">Sélectionner un client</option>
                                        <?php foreach ($data['clients'] as $client): ?>
                                            <option value="<?php echo $client['id']; ?>">
                                                <?php echo htmlspecialchars($client['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Numéro de commande</label>
                                    <input type="text" name="order_number" class="form-control"
                                           placeholder="CMD-2025-001">
                                </div>

                                <div class="form-group">
                                    <label>Description *</label>
                                    <textarea name="description" class="form-control" rows="3"
                                              placeholder="Carton contenant des appareils électroniques..." required></textarea>
                                </div>
                            </div>

                            <!-- Package Specifications -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3"><i class="fas fa-cube"></i> Spécifications du Colis</h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Longueur (cm) *</label>
                                            <input type="number" step="0.01" name="length" class="form-control"
                                                   placeholder="50" required min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Largeur (cm) *</label>
                                            <input type="number" step="0.01" name="width" class="form-control"
                                                   placeholder="40" required min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Hauteur (cm) *</label>
                                            <input type="number" step="0.01" name="height" class="form-control"
                                                   placeholder="30" required min="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Poids (kg) *</label>
                                    <input type="number" step="0.01" name="weight" class="form-control"
                                           placeholder="12.5" required min="0.1">
                                </div>

                                <div class="form-group">
                                    <label>Priorité</label>
                                    <select name="priority" class="form-control">
                                        <option value="low">Basse</option>
                                        <option value="normal" selected>Normale</option>
                                        <option value="high">Haute</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Contraintes</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_fragile" name="is_fragile" value="1">
                                        <label class="custom-control-label" for="is_fragile">
                                            <i class="fas fa-wine-glass-alt text-danger"></i> Fragile
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_stackable" name="is_stackable" value="1" checked>
                                        <label class="custom-control-label" for="is_stackable">
                                            <i class="fas fa-layer-group text-success"></i> Empilable
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="rotation_allowed" name="rotation_allowed" value="1" checked>
                                        <label class="custom-control-label" for="rotation_allowed">
                                            <i class="fas fa-sync-alt text-info"></i> Rotation autorisée
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <!-- Delivery Address -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3"><i class="fas fa-map-marker-alt"></i> Adresse de Livraison</h5>

                                <div class="form-group">
                                    <label>Adresse complète *</label>
                                    <textarea name="delivery_address" class="form-control" rows="3"
                                              placeholder="123 Rue de la République, Tunis 1000" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Latitude GPS</label>
                                            <input type="number" step="0.00000001" name="delivery_lat" class="form-control"
                                                   placeholder="36.8065">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Longitude GPS</label>
                                            <input type="number" step="0.00000001" name="delivery_lng" class="form-control"
                                                   placeholder="10.1815">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Contact *</label>
                                    <input type="text" name="delivery_contact" class="form-control"
                                           placeholder="Mohamed Ben Ali" required>
                                </div>

                                <div class="form-group">
                                    <label>Téléphone *</label>
                                    <input type="tel" name="delivery_phone" class="form-control"
                                           placeholder="+216 XX XXX XXX" required>
                                </div>
                            </div>

                            <!-- Delivery Schedule -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3"><i class="fas fa-clock"></i> Planification Livraison</h5>

                                <div class="alert alert-info">
                                    <small><i class="fas fa-info-circle"></i> Les fenêtres horaires sont optionnelles mais recommandées pour l'optimisation IA</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fenêtre - Début</label>
                                            <input type="time" name="time_window_start" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fenêtre - Fin</label>
                                            <input type="time" name="time_window_end" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Notes de livraison</label>
                                    <textarea name="delivery_notes" class="form-control" rows="4"
                                              placeholder="Instructions spéciales: sonner deux fois, livraison au 3ème étage..."></textarea>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-12 text-right">
                                <a href="<?php echo APP_URL; ?>/smart_delivery/packages" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Créer le colis
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
