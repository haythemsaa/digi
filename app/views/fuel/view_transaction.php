<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-receipt"></i> Transaction <?php echo htmlspecialchars($data['transaction']['transaction_number']); ?>
            </h1>
            <p class="text-muted">Détails de la transaction carburant</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel/transactions" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informations de Transaction
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Numéro:</strong><br>
                            <?php echo htmlspecialchars($data['transaction']['transaction_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date:</strong><br>
                            <?php echo date('d/m/Y H:i', strtotime($data['transaction']['transaction_date'])); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Véhicule:</strong><br>
                            <?php echo htmlspecialchars($data['transaction']['registration_number']); ?><br>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($data['transaction']['brand'] . ' ' . $data['transaction']['model']); ?>
                            </small></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Conducteur:</strong><br>
                            <?php echo htmlspecialchars($data['transaction']['driver_name'] ?? 'Non assigné'); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Type de Carburant:</strong><br>
                            <span class="badge badge-info badge-lg">
                                <?php echo strtoupper($data['transaction']['fuel_type']); ?>
                            </span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Carte Carburant:</strong><br>
                            <?php if ($data['transaction']['card_number']): ?>
                                <span class="badge badge-success">
                                    <?php echo htmlspecialchars($data['transaction']['card_number']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Aucune</span>
                            <?php endif; ?></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p><strong>Quantité:</strong><br>
                            <span class="h5"><?php echo number_format($data['transaction']['quantity_liters'], 2); ?> L</span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Prix Unitaire:</strong><br>
                            <span class="h5"><?php echo number_format($data['transaction']['unit_price'], 3); ?> TND</span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Montant Total:</strong><br>
                            <span class="h4 text-primary"><?php echo number_format($data['transaction']['total_amount'], 2); ?> TND</span></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kilométrage:</strong><br>
                            <?php echo $data['transaction']['odometer_reading'] ? number_format($data['transaction']['odometer_reading']) . ' km' : 'Non renseigné'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Plein Complet:</strong><br>
                            <?php if ($data['transaction']['is_full_tank']): ?>
                                <span class="badge badge-success">Oui</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Non</span>
                            <?php endif; ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Station:</strong><br>
                            <?php echo htmlspecialchars($data['transaction']['station_name'] ?? 'Non renseigné'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Méthode de Paiement:</strong><br>
                            <?php
                            $paymentMethods = [
                                'fuel_card' => 'Carte Carburant',
                                'cash' => 'Espèces',
                                'credit_card' => 'Carte Bancaire',
                                'invoice' => 'Facture'
                            ];
                            echo $paymentMethods[$data['transaction']['payment_method']] ?? $data['transaction']['payment_method'];
                            ?></p>
                        </div>
                    </div>

                    <?php if ($data['transaction']['notes']): ?>
                        <hr>
                        <p><strong>Notes:</strong><br>
                        <?php echo nl2br(htmlspecialchars($data['transaction']['notes'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-chart-line"></i> Informations</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <i class="fas fa-calendar"></i>
                        <strong>Enregistré:</strong><br>
                        <small><?php echo date('d/m/Y H:i', strtotime($data['transaction']['created_at'])); ?></small>
                    </p>

                    <?php if ($data['transaction']['receipt_number']): ?>
                        <p class="mb-2">
                            <i class="fas fa-file-invoice"></i>
                            <strong>N° Reçu:</strong><br>
                            <small><?php echo htmlspecialchars($data['transaction']['receipt_number']); ?></small>
                        </p>
                    <?php endif; ?>

                    <?php if ($data['transaction']['latitude'] && $data['transaction']['longitude']): ?>
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <strong>Position GPS:</strong><br>
                            <small>
                                <?php echo number_format($data['transaction']['latitude'], 6); ?>,
                                <?php echo number_format($data['transaction']['longitude'], 6); ?>
                            </small>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
