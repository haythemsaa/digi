<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-file-alt"></i> Réservation: <?php echo htmlspecialchars($data['booking']['booking_number']); ?>
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/bookings" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux Réservations
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Booking Details -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-info-circle"></i> Détails de la Réservation</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Numéro de Réservation:</th>
                            <td><strong class="text-primary"><?php echo htmlspecialchars($data['booking']['booking_number']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Type de Service:</th>
                            <td>
                                <?php
                                $typeIcons = [
                                    'taxi' => '<i class="fas fa-taxi text-warning"></i> Taxi',
                                    'bus' => '<i class="fas fa-bus text-primary"></i> Bus',
                                    'shuttle' => '<i class="fas fa-shuttle-van text-info"></i> Navette',
                                    'corporate' => '<i class="fas fa-briefcase text-success"></i> Corporate'
                                ];
                                echo $typeIcons[$data['booking']['booking_type']] ?? $data['booking']['booking_type'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Type de Course:</th>
                            <td>
                                <?php
                                $rideTypes = [
                                    'immediate' => 'Immédiate',
                                    'scheduled' => 'Programmée',
                                    'recurring' => 'Récurrente'
                                ];
                                echo $rideTypes[$data['booking']['ride_type']] ?? $data['booking']['ride_type'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                <?php
                                $statusClasses = [
                                    'pending' => 'secondary',
                                    'confirmed' => 'info',
                                    'driver_assigned' => 'primary',
                                    'driver_arrived' => 'warning',
                                    'in_progress' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    'no_show' => 'dark'
                                ];
                                $statusTexts = [
                                    'pending' => 'En Attente',
                                    'confirmed' => 'Confirmé',
                                    'driver_assigned' => 'Chauffeur Assigné',
                                    'driver_arrived' => 'Chauffeur Arrivé',
                                    'in_progress' => 'En Cours',
                                    'completed' => 'Complété',
                                    'cancelled' => 'Annulé',
                                    'no_show' => 'Absent'
                                ];
                                $badgeClass = $statusClasses[$data['booking']['status']] ?? 'secondary';
                                $statusText = $statusTexts[$data['booking']['status']] ?? $data['booking']['status'];
                                ?>
                                <span class="badge badge-<?php echo $badgeClass; ?> p-2">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Date de Création:</th>
                            <td><?php echo date('d/m/Y à H:i', strtotime($data['booking']['created_at'])); ?></td>
                        </tr>
                        <?php if ($data['booking']['scheduled_pickup_time']): ?>
                            <tr>
                                <th>Heure Programmée:</th>
                                <td>
                                    <strong><?php echo date('d/m/Y à H:i', strtotime($data['booking']['scheduled_pickup_time'])); ?></strong>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Trip Details -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-route"></i> Détails du Trajet</h6>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-map-marker-alt text-success"></i> Point de Départ</h6>
                    <p class="ml-3"><?php echo nl2br(htmlspecialchars($data['booking']['pickup_address'])); ?></p>

                    <?php if ($data['booking']['pickup_latitude'] && $data['booking']['pickup_longitude']): ?>
                        <p class="ml-3 text-muted">
                            <small>
                                <i class="fas fa-crosshairs"></i>
                                GPS: <?php echo $data['booking']['pickup_latitude']; ?>, <?php echo $data['booking']['pickup_longitude']; ?>
                            </small>
                        </p>
                    <?php endif; ?>

                    <hr>

                    <h6><i class="fas fa-map-marker-alt text-danger"></i> Point d'Arrivée</h6>
                    <p class="ml-3"><?php echo nl2br(htmlspecialchars($data['booking']['dropoff_address'])); ?></p>

                    <?php if ($data['booking']['dropoff_latitude'] && $data['booking']['dropoff_longitude']): ?>
                        <p class="ml-3 text-muted">
                            <small>
                                <i class="fas fa-crosshairs"></i>
                                GPS: <?php echo $data['booking']['dropoff_latitude']; ?>, <?php echo $data['booking']['dropoff_longitude']; ?>
                            </small>
                        </p>
                    <?php endif; ?>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Distance Estimée:</strong> <?php echo number_format($data['booking']['estimated_distance_km'], 1); ?> km</p>
                            <?php if ($data['booking']['actual_distance_km']): ?>
                                <p><strong>Distance Réelle:</strong> <?php echo number_format($data['booking']['actual_distance_km'], 1); ?> km</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Durée Estimée:</strong> <?php echo $data['booking']['estimated_duration_minutes']; ?> min</p>
                            <?php if ($data['booking']['actual_duration_minutes']): ?>
                                <p><strong>Durée Réelle:</strong> <?php echo $data['booking']['actual_duration_minutes']; ?> min</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($data['booking']['passenger_count'] > 1): ?>
                        <p><strong>Nombre de Passagers:</strong> <?php echo $data['booking']['passenger_count']; ?></p>
                    <?php endif; ?>

                    <?php if ($data['booking']['special_requirements']): ?>
                        <p>
                            <strong>Exigences Spéciales:</strong><br>
                            <?php echo nl2br(htmlspecialchars($data['booking']['special_requirements'])); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Times -->
            <?php if ($data['booking']['actual_pickup_time'] || $data['booking']['actual_dropoff_time']): ?>
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-clock"></i> Horaires</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($data['booking']['actual_pickup_time']): ?>
                            <p><strong>Prise en charge:</strong> <?php echo date('d/m/Y à H:i', strtotime($data['booking']['actual_pickup_time'])); ?></p>
                        <?php endif; ?>
                        <?php if ($data['booking']['actual_dropoff_time']): ?>
                            <p><strong>Arrivée:</strong> <?php echo date('d/m/Y à H:i', strtotime($data['booking']['actual_dropoff_time'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Passenger & Driver Info -->
        <div class="col-md-4">
            <!-- Passenger Info -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-user"></i> Passager</h6>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($data['booking']['passenger_first_name'] . ' ' . $data['booking']['passenger_last_name']); ?></h5>
                    <p class="mb-1">
                        <i class="fas fa-phone text-success"></i>
                        <a href="tel:<?php echo $data['booking']['passenger_phone']; ?>">
                            <?php echo htmlspecialchars($data['booking']['passenger_phone']); ?>
                        </a>
                    </p>
                    <?php if ($data['booking']['passenger_email']): ?>
                        <p class="mb-0">
                            <i class="fas fa-envelope text-info"></i>
                            <a href="mailto:<?php echo $data['booking']['passenger_email']; ?>">
                                <?php echo htmlspecialchars($data['booking']['passenger_email']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Driver Info -->
            <?php if ($data['booking']['driver_first_name']): ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="m-0"><i class="fas fa-id-card"></i> Chauffeur</h6>
                    </div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($data['booking']['driver_first_name'] . ' ' . $data['booking']['driver_last_name']); ?></h5>
                        <?php if ($data['booking']['license_plate']): ?>
                            <p class="mb-1">
                                <i class="fas fa-car"></i>
                                <strong><?php echo htmlspecialchars($data['booking']['license_plate']); ?></strong>
                            </p>
                        <?php endif; ?>
                        <?php if ($data['booking']['vehicle_class']): ?>
                            <p class="mb-0">
                                <i class="fas fa-star"></i>
                                Classe: <?php echo ucfirst($data['booking']['vehicle_class']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Aucun chauffeur assigné pour le moment.
                </div>
            <?php endif; ?>

            <!-- Pricing -->
            <div class="card shadow mb-4 border-left-info">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-money-bill-wave"></i> Tarification</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Tarif Estimé:</th>
                            <td class="text-right"><?php echo number_format($data['booking']['estimated_fare'], 2); ?> TND</td>
                        </tr>
                        <?php if ($data['booking']['actual_fare']): ?>
                            <tr>
                                <th>Tarif Réel:</th>
                                <td class="text-right"><?php echo number_format($data['booking']['actual_fare'], 2); ?> TND</td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($data['booking']['discount_amount'] > 0): ?>
                            <tr class="text-success">
                                <th>Réduction:</th>
                                <td class="text-right">-<?php echo number_format($data['booking']['discount_amount'], 2); ?> TND</td>
                            </tr>
                        <?php endif; ?>
                        <tr class="font-weight-bold">
                            <th>Montant Final:</th>
                            <td class="text-right">
                                <h5 class="mb-0 text-primary"><?php echo number_format($data['booking']['final_amount'], 2); ?> TND</h5>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <p class="mb-1">
                        <strong>Mode de Paiement:</strong>
                        <?php
                        $paymentMethods = [
                            'cash' => 'Espèces',
                            'card' => 'Carte Bancaire',
                            'mobile_money' => 'Mobile Money',
                            'account' => 'Compte Client',
                            'voucher' => 'Bon/Voucher'
                        ];
                        echo $paymentMethods[$data['booking']['payment_method']] ?? $data['booking']['payment_method'];
                        ?>
                    </p>
                    <p class="mb-0">
                        <strong>Statut Paiement:</strong>
                        <?php
                        $paymentClasses = [
                            'pending' => 'warning',
                            'paid' => 'success',
                            'failed' => 'danger',
                            'refunded' => 'info'
                        ];
                        $paymentTexts = [
                            'pending' => 'En attente',
                            'paid' => 'Payé',
                            'failed' => 'Échoué',
                            'refunded' => 'Remboursé'
                        ];
                        $paymentBadge = $paymentClasses[$data['booking']['payment_status']] ?? 'secondary';
                        $paymentText = $paymentTexts[$data['booking']['payment_status']] ?? $data['booking']['payment_status'];
                        ?>
                        <span class="badge badge-<?php echo $paymentBadge; ?>">
                            <?php echo $paymentText; ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Status Update Actions -->
            <?php if ($data['booking']['status'] != 'completed' && $data['booking']['status'] != 'cancelled'): ?>
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-cog"></i> Actions</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo APP_URL; ?>/passenger_transport/updateBookingStatus/<?php echo $data['booking']['id']; ?>">
                            <div class="form-group">
                                <label for="status">Changer le Statut</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="">-- Sélectionner --</option>
                                    <?php if ($data['booking']['status'] == 'pending'): ?>
                                        <option value="confirmed">Confirmé</option>
                                        <option value="driver_assigned">Chauffeur Assigné</option>
                                        <option value="cancelled">Annulé</option>
                                    <?php elseif ($data['booking']['status'] == 'driver_assigned'): ?>
                                        <option value="driver_arrived">Chauffeur Arrivé</option>
                                        <option value="in_progress">En Cours</option>
                                        <option value="cancelled">Annulé</option>
                                    <?php elseif ($data['booking']['status'] == 'in_progress'): ?>
                                        <option value="completed">Complété</option>
                                        <option value="cancelled">Annulé</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-check"></i> Mettre à Jour
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
