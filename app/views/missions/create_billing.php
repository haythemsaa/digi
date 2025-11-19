<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar"></i> Facturation Mission
                <?php echo htmlspecialchars($data['mission']['mission_number']); ?>
            </h1>
            <p class="text-muted">Créer une facture pour cette mission</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/missions/view/<?php echo $data['mission']['id']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/missions/createBilling/<?php echo $data['mission']['id']; ?>">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-calculator"></i> Détails de Facturation
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Client:</strong> <?php echo htmlspecialchars($data['mission']['client_name']); ?>
                        </div>

                        <div class="form-group">
                            <label>Tarif de Base (TND) *</label>
                            <input type="number" step="0.01" class="form-control" name="base_rate"
                                   id="baseRate" placeholder="100.00" required>
                            <small class="form-text text-muted">Tarif forfaitaire de base pour la mission</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Frais de Distance (TND)</label>
                                    <input type="number" step="0.01" class="form-control" name="distance_charge"
                                           id="distanceCharge" placeholder="0.00" value="0">
                                    <small class="form-text text-muted">
                                        <?php if ($data['mission']['estimated_distance_km']): ?>
                                            Distance estimée: <?php echo number_format($data['mission']['estimated_distance_km'], 1); ?> km
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Frais de Temps (TND)</label>
                                    <input type="number" step="0.01" class="form-control" name="time_charge"
                                           id="timeCharge" placeholder="0.00" value="0">
                                    <small class="form-text text-muted">
                                        <?php if ($data['mission']['estimated_duration_minutes']): ?>
                                            Durée estimée: <?php echo $data['mission']['estimated_duration_minutes']; ?> minutes
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Frais Additionnels (TND)</label>
                            <input type="number" step="0.01" class="form-control" name="additional_charges"
                                   id="additionalCharges" placeholder="0.00" value="0">
                            <small class="form-text text-muted">Péages, parking, manutention, etc.</small>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Taux de TVA (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="tax_rate"
                                           id="taxRate" placeholder="20.00" value="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remise (TND)</label>
                                    <input type="number" step="0.01" class="form-control" name="discount_amount"
                                           id="discountAmount" placeholder="0.00" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-file-invoice"></i> Aperçu Facture</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Tarif de Base</th>
                                <td class="text-right" id="previewBase">0.00 TND</td>
                            </tr>
                            <tr>
                                <th>Frais Distance</th>
                                <td class="text-right" id="previewDistance">0.00 TND</td>
                            </tr>
                            <tr>
                                <th>Frais Temps</th>
                                <td class="text-right" id="previewTime">0.00 TND</td>
                            </tr>
                            <tr>
                                <th>Frais Additionnels</th>
                                <td class="text-right" id="previewAdditional">0.00 TND</td>
                            </tr>
                            <tr class="table-light">
                                <th>Sous-total</th>
                                <td class="text-right"><strong id="previewSubtotal">0.00 TND</strong></td>
                            </tr>
                            <tr>
                                <th>TVA <span id="previewTaxRate">(20%)</span></th>
                                <td class="text-right" id="previewTax">0.00 TND</td>
                            </tr>
                            <tr>
                                <th>Remise</th>
                                <td class="text-right text-danger" id="previewDiscount">0.00 TND</td>
                            </tr>
                            <tr class="table-success">
                                <th><strong>TOTAL</strong></th>
                                <td class="text-right">
                                    <strong id="previewTotal" style="font-size: 1.2em;">0.00 TND</strong>
                                </td>
                            </tr>
                        </table>

                        <hr>

                        <p class="small text-muted mb-0">
                            <i class="fas fa-calendar"></i>
                            Date de facturation: <?php echo date('d/m/Y'); ?>
                        </p>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-save"></i> Créer la Facture
                        </button>
                        <a href="<?php echo APP_URL; ?>/missions/view/<?php echo $data['mission']['id']; ?>"
                           class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>

                <div class="card shadow mt-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0"><i class="fas fa-lightbulb"></i> Conseil</h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-0">
                            Calculez les frais de distance en multipliant la distance par votre tarif kilométrique.
                            Par exemple: 50 km × 0.50 TND = 25.00 TND
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function calculateTotal() {
    const baseRate = parseFloat($('#baseRate').val()) || 0;
    const distanceCharge = parseFloat($('#distanceCharge').val()) || 0;
    const timeCharge = parseFloat($('#timeCharge').val()) || 0;
    const additionalCharges = parseFloat($('#additionalCharges').val()) || 0;
    const taxRate = parseFloat($('#taxRate').val()) || 0;
    const discountAmount = parseFloat($('#discountAmount').val()) || 0;

    const subtotal = baseRate + distanceCharge + timeCharge + additionalCharges;
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount - discountAmount;

    // Update preview
    $('#previewBase').text(baseRate.toFixed(2) + ' TND');
    $('#previewDistance').text(distanceCharge.toFixed(2) + ' TND');
    $('#previewTime').text(timeCharge.toFixed(2) + ' TND');
    $('#previewAdditional').text(additionalCharges.toFixed(2) + ' TND');
    $('#previewSubtotal').text(subtotal.toFixed(2) + ' TND');
    $('#previewTaxRate').text('(' + taxRate.toFixed(2) + '%)');
    $('#previewTax').text(taxAmount.toFixed(2) + ' TND');
    $('#previewDiscount').text(discountAmount > 0 ? '-' + discountAmount.toFixed(2) + ' TND' : '0.00 TND');
    $('#previewTotal').text(total.toFixed(2) + ' TND');
}

// Calculate on input change
$('#baseRate, #distanceCharge, #timeCharge, #additionalCharges, #taxRate, #discountAmount').on('input', calculateTotal);

// Initial calculation
calculateTotal();
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
