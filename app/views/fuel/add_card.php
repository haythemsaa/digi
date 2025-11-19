<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-plus"></i> Nouvelle Carte Carburant</h1>
            <p class="text-muted">Enregistrer une nouvelle carte</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel/cards" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-credit-card"></i> Informations de la Carte
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/fuel/addCard">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Numéro de Carte *</label>
                                    <input type="text" class="form-control" name="card_number"
                                           placeholder="1234-5678-9012-3456" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de Carte</label>
                                    <select class="form-control" name="card_type">
                                        <option value="physical">Physique</option>
                                        <option value="virtual">Virtuelle</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Fournisseur</label>
                            <input type="text" class="form-control" name="provider"
                                   placeholder="Shell, Total, Agil...">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Véhicule Assigné</label>
                                    <select class="form-control" name="vehicle_id">
                                        <option value="">Non assigné</option>
                                        <?php foreach ($data['vehicles'] as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>">
                                                <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['brand'] . ' ' . $vehicle['model']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Laisser vide pour carte partagée</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Conducteur Assigné</label>
                                    <select class="form-control" name="driver_id">
                                        <option value="">Non assigné</option>
                                        <!-- Will be populated with drivers -->
                                    </select>
                                    <small class="form-text text-muted">Optionnel</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Limite Journalière (TND)</label>
                                    <input type="number" step="0.01" class="form-control" name="daily_limit"
                                           placeholder="200.00">
                                    <small class="form-text text-muted">Laisser vide pour illimité</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Limite Mensuelle (TND)</label>
                                    <input type="number" step="0.01" class="form-control" name="monthly_limit"
                                           placeholder="5000.00">
                                    <small class="form-text text-muted">Laisser vide pour illimité</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date d'Émission</label>
                                    <input type="date" class="form-control" name="issue_date"
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date d'Expiration</label>
                                    <input type="date" class="form-control" name="expiry_date">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isActive"
                                       name="is_active" value="1" checked>
                                <label class="custom-control-label" for="isActive">Carte Active</label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Les limites quotidiennes et mensuelles permettent de contrôler
                            les dépenses. Des alertes seront générées si ces limites sont dépassées.
                        </div>

                        <div class="form-group text-right">
                            <a href="<?php echo APP_URL; ?>/fuel/cards" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
