<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-tasks"></i> Mission <?php echo htmlspecialchars($data['mission']['mission_number']); ?>
            </h1>
            <p class="text-muted"><?php echo htmlspecialchars($data['mission']['client_name']); ?></p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/missions/edit/<?php echo $data['mission']['id']; ?>"
               class="btn btn-warning">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="<?php echo APP_URL; ?>/missions" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Détails de la Mission
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Numéro:</strong><br>
                            <?php echo htmlspecialchars($data['mission']['mission_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Type:</strong><br>
                            <?php
                            $typeLabels = [
                                'delivery' => 'Livraison',
                                'pickup' => 'Ramassage',
                                'transport' => 'Transport',
                                'service' => 'Service',
                                'maintenance' => 'Maintenance',
                                'other' => 'Autre'
                            ];
                            echo $typeLabels[$data['mission']['mission_type']] ?? $data['mission']['mission_type'];
                            ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Priorité:</strong><br>
                            <?php
                            $priorityBadges = [
                                'low' => 'secondary',
                                'normal' => 'info',
                                'high' => 'warning',
                                'urgent' => 'danger'
                            ];
                            $badge = $priorityBadges[$data['mission']['priority']] ?? 'secondary';
                            ?>
                            <span class="badge badge-<?php echo $badge; ?> badge-lg">
                                <?php echo strtoupper($data['mission']['priority']); ?>
                            </span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Statut:</strong><br>
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
                        </div>
                    </div>

                    <?php if ($data['mission']['description']): ?>
                        <hr>
                        <p><strong>Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($data['mission']['description'])); ?></p>
                    <?php endif; ?>

                    <hr>

                    <h6><i class="fas fa-user"></i> Informations Client</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Nom:</strong> <?php echo htmlspecialchars($data['mission']['client_name']); ?></p>
                            <?php if ($data['mission']['client_phone']): ?>
                                <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($data['mission']['client_phone']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($data['mission']['client_email']): ?>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($data['mission']['client_email']); ?></p>
                            <?php endif; ?>
                            <?php if ($data['mission']['client_address']): ?>
                                <p><strong>Adresse:</strong> <?php echo htmlspecialchars($data['mission']['client_address']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <h6><i class="fas fa-map-marker-alt"></i> Lieux</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php if ($data['mission']['pickup_location']): ?>
                                <p><strong>Départ:</strong><br>
                                <i class="fas fa-map-marker-alt text-info"></i>
                                <?php echo htmlspecialchars($data['mission']['pickup_location']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($data['mission']['delivery_location']): ?>
                                <p><strong>Arrivée:</strong><br>
                                <i class="fas fa-map-marker-alt text-success"></i>
                                <?php echo htmlspecialchars($data['mission']['delivery_location']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($data['mission']['estimated_distance_km'] || $data['mission']['estimated_duration_minutes']): ?>
                        <div class="row">
                            <?php if ($data['mission']['estimated_distance_km']): ?>
                                <div class="col-md-6">
                                    <p><strong>Distance estimée:</strong>
                                    <?php echo number_format($data['mission']['estimated_distance_km'], 1); ?> km</p>
                                </div>
                            <?php endif; ?>
                            <?php if ($data['mission']['estimated_duration_minutes']): ?>
                                <div class="col-md-6">
                                    <p><strong>Durée estimée:</strong>
                                    <?php echo $data['mission']['estimated_duration_minutes']; ?> minutes</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <h6><i class="fas fa-car"></i> Affectation</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Véhicule:</strong><br>
                            <?php if ($data['mission']['registration_number']): ?>
                                <?php echo htmlspecialchars($data['mission']['registration_number']); ?>
                                <small class="text-muted">
                                    (<?php echo htmlspecialchars($data['mission']['brand'] . ' ' . $data['mission']['model']); ?>)
                                </small>
                            <?php else: ?>
                                <span class="text-muted">Non assigné</span>
                            <?php endif; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Conducteur:</strong><br>
                            <?php if ($data['mission']['driver_name']): ?>
                                <?php echo htmlspecialchars($data['mission']['driver_name']); ?>
                                <?php if ($data['mission']['driver_phone']): ?>
                                    <br><small><?php echo htmlspecialchars($data['mission']['driver_phone']); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Non assigné</span>
                            <?php endif; ?></p>
                        </div>
                    </div>

                    <?php if ($data['mission']['notes']): ?>
                        <hr>
                        <p><strong>Notes:</strong><br>
                        <?php echo nl2br(htmlspecialchars($data['mission']['notes'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mission Items -->
            <?php if (!empty($data['items'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-boxes"></i> Articles
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Article</th>
                                        <th>Description</th>
                                        <th>Quantité</th>
                                        <th>Poids</th>
                                        <th>Fragile</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['item_description'] ?? '-'); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>
                                                <?php echo $item['weight_kg'] ? number_format($item['weight_kg'], 2) . ' kg' : '-'; ?>
                                            </td>
                                            <td>
                                                <?php if ($item['fragile']): ?>
                                                    <i class="fas fa-exclamation-triangle text-warning"></i> Oui
                                                <?php else: ?>
                                                    Non
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Billing -->
            <?php if ($data['billing']): ?>
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-file-invoice-dollar"></i> Facturation
                        </h6>
                    </div>
                    <div class="card-body">
                        <p><strong>N° Facture:</strong> <?php echo htmlspecialchars($data['billing']['invoice_number']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($data['billing']['invoice_date'])); ?></p>

                        <table class="table table-bordered mt-3">
                            <tr>
                                <th>Tarif de Base</th>
                                <td><?php echo number_format($data['billing']['base_rate'], 2); ?> TND</td>
                            </tr>
                            <tr>
                                <th>Frais Distance</th>
                                <td><?php echo number_format($data['billing']['distance_charge'], 2); ?> TND</td>
                            </tr>
                            <tr>
                                <th>Frais Temps</th>
                                <td><?php echo number_format($data['billing']['time_charge'], 2); ?> TND</td>
                            </tr>
                            <tr>
                                <th>Frais Additionnels</th>
                                <td><?php echo number_format($data['billing']['additional_charges'], 2); ?> TND</td>
                            </tr>
                            <tr>
                                <th>Sous-total</th>
                                <td><?php echo number_format($data['billing']['subtotal'], 2); ?> TND</td>
                            </tr>
                            <tr>
                                <th>TVA (<?php echo $data['billing']['tax_rate']; ?>%)</th>
                                <td><?php echo number_format($data['billing']['tax_amount'], 2); ?> TND</td>
                            </tr>
                            <?php if ($data['billing']['discount_amount'] > 0): ?>
                                <tr>
                                    <th>Remise</th>
                                    <td class="text-danger">-<?php echo number_format($data['billing']['discount_amount'], 2); ?> TND</td>
                                </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <th><strong>TOTAL</strong></th>
                                <td><strong><?php echo number_format($data['billing']['total_amount'], 2); ?> TND</strong></td>
                            </tr>
                        </table>

                        <p>
                            <strong>Statut Paiement:</strong>
                            <?php
                            $paymentBadge = $data['billing']['payment_status'] === 'paid' ? 'success' : 'warning';
                            ?>
                            <span class="badge badge-<?php echo $paymentBadge; ?>">
                                <?php echo ucfirst($data['billing']['payment_status']); ?>
                            </span>
                        </p>

                        <?php if ($data['billing']['payment_status'] !== 'paid'): ?>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#paymentModal">
                                <i class="fas fa-check"></i> Marquer comme Payé
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <p class="text-muted">Aucune facture générée pour cette mission.</p>
                        <a href="<?php echo APP_URL; ?>/missions/createBilling/<?php echo $data['mission']['id']; ?>"
                           class="btn btn-info">
                            <i class="fas fa-file-invoice-dollar"></i> Créer une Facture
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Timeline -->
            <?php if (!empty($data['updates'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-secondary">
                            <i class="fas fa-history"></i> Historique
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($data['updates'] as $update): ?>
                                <div class="timeline-item mb-3">
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($update['created_at'])); ?>
                                        <?php if ($update['updated_by_name']): ?>
                                            - Par <?php echo htmlspecialchars($update['updated_by_name']); ?>
                                        <?php endif; ?>
                                    </small>
                                    <p><?php echo htmlspecialchars($update['message']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Status Management -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-cog"></i> Gestion du Statut</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo APP_URL; ?>/missions/updateStatus/<?php echo $data['mission']['id']; ?>">
                        <div class="form-group">
                            <label>Changer le Statut</label>
                            <select class="form-control" name="status">
                                <option value="pending" <?php echo $data['mission']['status'] === 'pending' ? 'selected' : ''; ?>>
                                    En attente
                                </option>
                                <option value="assigned" <?php echo $data['mission']['status'] === 'assigned' ? 'selected' : ''; ?>>
                                    Assignée
                                </option>
                                <option value="in_progress" <?php echo $data['mission']['status'] === 'in_progress' ? 'selected' : ''; ?>>
                                    En cours
                                </option>
                                <option value="completed" <?php echo $data['mission']['status'] === 'completed' ? 'selected' : ''; ?>>
                                    Terminée
                                </option>
                                <option value="on_hold" <?php echo $data['mission']['status'] === 'on_hold' ? 'selected' : ''; ?>>
                                    En pause
                                </option>
                                <option value="cancelled" <?php echo $data['mission']['status'] === 'cancelled' ? 'selected' : ''; ?>>
                                    Annulée
                                </option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Mettre à Jour
                        </button>
                    </form>
                </div>
            </div>

            <!-- Schedule Info -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-calendar"></i> Planning</h6>
                </div>
                <div class="card-body">
                    <?php if ($data['mission']['scheduled_start']): ?>
                        <p><strong>Début planifié:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($data['mission']['scheduled_start'])); ?></p>
                    <?php endif; ?>

                    <?php if ($data['mission']['scheduled_end']): ?>
                        <p><strong>Fin planifiée:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($data['mission']['scheduled_end'])); ?></p>
                    <?php endif; ?>

                    <?php if ($data['mission']['actual_start']): ?>
                        <hr>
                        <p><strong>Début réel:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($data['mission']['actual_start'])); ?></p>
                    <?php endif; ?>

                    <?php if ($data['mission']['actual_end']): ?>
                        <p><strong>Fin réelle:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($data['mission']['actual_end'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<?php if (isset($data['billing']) && $data['billing']['payment_status'] !== 'paid'): ?>
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enregistrer le Paiement</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="<?php echo APP_URL; ?>/missions/markPaid/<?php echo $data['billing']['id']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Méthode de Paiement</label>
                        <select class="form-control" name="payment_method" required>
                            <option value="cash">Espèces</option>
                            <option value="bank_transfer">Virement Bancaire</option>
                            <option value="check">Chèque</option>
                            <option value="credit_card">Carte Bancaire</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Référence de Paiement</label>
                        <input type="text" class="form-control" name="payment_reference"
                               placeholder="N° de transaction, chèque, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer le Paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once '../app/views/includes/footer.php'; ?>
