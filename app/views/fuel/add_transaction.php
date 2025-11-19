<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-plus"></i> Nouvelle Transaction Carburant</h1>
            <p class="text-muted">Enregistrer un ravitaillement</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel/transactions" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-gas-pump"></i> Détails de la Transaction
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/fuel/addTransaction">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date et Heure *</label>
                                    <input type="datetime-local" class="form-control" name="transaction_date"
                                           value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Véhicule *</label>
                                    <select class="form-control" name="vehicle_id" required>
                                        <option value="">Sélectionner un véhicule</option>
                                        <?php foreach ($data['vehicles'] as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>">
                                                <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['brand'] . ' ' . $vehicle['model']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de Carburant *</label>
                                    <select class="form-control" name="fuel_type" required>
                                        <option value="diesel">Diesel</option>
                                        <option value="gasoline">Essence</option>
                                        <option value="lpg">GPL</option>
                                        <option value="cng">GNC</option>
                                        <option value="electric">Électrique</option>
                                        <option value="hybrid">Hybride</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Carte Carburant</label>
                                    <select class="form-control" name="fuel_card_id">
                                        <option value="">Aucune</option>
                                        <?php foreach ($data['fuel_cards'] as $card): ?>
                                            <?php if ($card['is_active']): ?>
                                                <option value="<?php echo $card['id']; ?>">
                                                    <?php echo htmlspecialchars($card['card_number'] . ' - ' . ($card['provider'] ?? 'N/A')); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Quantité (Litres) *</label>
                                    <input type="number" step="0.01" class="form-control" name="quantity_liters"
                                           placeholder="50.00" required id="quantity">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Prix Unitaire (TND) *</label>
                                    <input type="number" step="0.001" class="form-control" name="unit_price"
                                           placeholder="2.150" required id="unitPrice">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Montant Total (TND)</label>
                                    <input type="text" class="form-control" id="totalAmount" readonly
                                           placeholder="Calculé automatiquement">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kilométrage</label>
                                    <input type="number" class="form-control" name="odometer_reading"
                                           placeholder="125000">
                                    <small class="form-text text-muted">Pour calcul de consommation</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Station</label>
                                    <input type="text" class="form-control" name="station_name"
                                           placeholder="Shell, Total, Agil...">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Méthode de Paiement</label>
                                    <select class="form-control" name="payment_method">
                                        <option value="fuel_card">Carte Carburant</option>
                                        <option value="cash">Espèces</option>
                                        <option value="credit_card">Carte Bancaire</option>
                                        <option value="invoice">Facture</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="isFullTank"
                                               name="is_full_tank" value="1" checked>
                                        <label class="custom-control-label" for="isFullTank">Plein complet</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" rows="3"
                                      placeholder="Observations, remarques..."></textarea>
                        </div>

                        <div class="form-group text-right">
                            <a href="<?php echo APP_URL; ?>/fuel/transactions" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate total amount
function calculateTotal() {
    const quantity = parseFloat($('#quantity').val()) || 0;
    const unitPrice = parseFloat($('#unitPrice').val()) || 0;
    const total = quantity * unitPrice;
    $('#totalAmount').val(total.toFixed(2) + ' TND');
}

$('#quantity, #unitPrice').on('input', calculateTotal);
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
