<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit"></i> Modifier Mission <?php echo htmlspecialchars($data['mission']['mission_number']); ?>
            </h1>
            <p class="text-muted">Mettre à jour les informations de la mission</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/missions/view/<?php echo $data['mission']['id']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/missions/edit/<?php echo $data['mission']['id']; ?>">
        <div class="row">
            <div class="col-md-8">
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
                                        <option value="delivery" <?php echo $data['mission']['mission_type'] === 'delivery' ? 'selected' : ''; ?>>
                                            Livraison
                                        </option>
                                        <option value="pickup" <?php echo $data['mission']['mission_type'] === 'pickup' ? 'selected' : ''; ?>>
                                            Ramassage
                                        </option>
                                        <option value="transport" <?php echo $data['mission']['mission_type'] === 'transport' ? 'selected' : ''; ?>>
                                            Transport
                                        </option>
                                        <option value="service" <?php echo $data['mission']['mission_type'] === 'service' ? 'selected' : ''; ?>>
                                            Service
                                        </option>
                                        <option value="maintenance" <?php echo $data['mission']['mission_type'] === 'maintenance' ? 'selected' : ''; ?>>
                                            Maintenance
                                        </option>
                                        <option value="other" <?php echo $data['mission']['mission_type'] === 'other' ? 'selected' : ''; ?>>
                                            Autre
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Priorité</label>
                                    <select class="form-control" name="priority">
                                        <option value="low" <?php echo $data['mission']['priority'] === 'low' ? 'selected' : ''; ?>>
                                            Basse
                                        </option>
                                        <option value="normal" <?php echo $data['mission']['priority'] === 'normal' ? 'selected' : ''; ?>>
                                            Normale
                                        </option>
                                        <option value="high" <?php echo $data['mission']['priority'] === 'high' ? 'selected' : ''; ?>>
                                            Haute
                                        </option>
                                        <option value="urgent" <?php echo $data['mission']['priority'] === 'urgent' ? 'selected' : ''; ?>>
                                            Urgente
                                        </option>
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
                                           value="<?php echo htmlspecialchars($data['mission']['client_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Téléphone</label>
                                    <input type="tel" class="form-control" name="client_phone"
                                           value="<?php echo htmlspecialchars($data['mission']['client_phone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="client_email"
                                           value="<?php echo htmlspecialchars($data['mission']['client_email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Adresse</label>
                                    <input type="text" class="form-control" name="client_address"
                                           value="<?php echo htmlspecialchars($data['mission']['client_address'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($data['mission']['description'] ?? ''); ?></textarea>
                        </div>

                        <h6 class="mt-3"><i class="fas fa-map-marker-alt"></i> Lieux</h6>
                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lieu de Départ/Ramassage</label>
                                    <input type="text" class="form-control" name="pickup_location"
                                           value="<?php echo htmlspecialchars($data['mission']['pickup_location'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lieu de Livraison/Arrivée</label>
                                    <input type="text" class="form-control" name="delivery_location"
                                           value="<?php echo htmlspecialchars($data['mission']['delivery_location'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Distance Estimée (km)</label>
                                    <input type="number" step="0.1" class="form-control" name="estimated_distance_km"
                                           value="<?php echo $data['mission']['estimated_distance_km'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Durée Estimée (minutes)</label>
                                    <input type="number" class="form-control" name="estimated_duration_minutes"
                                           value="<?php echo $data['mission']['estimated_duration_minutes'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3"><i class="fas fa-calendar"></i> Planification</h6>
                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Début Planifié</label>
                                    <input type="datetime-local" class="form-control" name="scheduled_start"
                                           value="<?php echo $data['mission']['scheduled_start'] ? date('Y-m-d\TH:i', strtotime($data['mission']['scheduled_start'])) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fin Planifiée</label>
                                    <input type="datetime-local" class="form-control" name="scheduled_end"
                                           value="<?php echo $data['mission']['scheduled_end'] ? date('Y-m-d\TH:i', strtotime($data['mission']['scheduled_end'])) : ''; ?>">
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
                                            <option value="<?php echo $vehicle['id']; ?>"
                                                <?php echo $data['mission']['assigned_vehicle_id'] == $vehicle['id'] ? 'selected' : ''; ?>>
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
                            <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($data['mission']['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> Information</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>N° Mission:</strong><br>
                        <?php echo htmlspecialchars($data['mission']['mission_number']); ?></p>

                        <p><strong>Statut Actuel:</strong><br>
                        <?php
                        $statusBadges = [
                            'pending' => 'warning',
                            'assigned' => 'info',
                            'in_progress' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'on_hold' => 'secondary'
                        ];
                        $statusLabels = [
                            'pending' => 'En attente',
                            'assigned' => 'Assignée',
                            'in_progress' => 'En cours',
                            'completed' => 'Terminée',
                            'cancelled' => 'Annulée',
                            'on_hold' => 'En pause'
                        ];
                        $badge = $statusBadges[$data['mission']['status']] ?? 'secondary';
                        $label = $statusLabels[$data['mission']['status']] ?? $data['mission']['status'];
                        ?>
                        <span class="badge badge-<?php echo $badge; ?> badge-lg">
                            <?php echo $label; ?>
                        </span></p>

                        <p><strong>Créée le:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($data['mission']['created_at'])); ?></p>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            <small>Pour changer le statut, utilisez la page de visualisation de la mission.</small>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Enregistrer les Modifications
                        </button>
                        <a href="<?php echo APP_URL; ?>/missions/view/<?php echo $data['mission']['id']; ?>"
                           class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
