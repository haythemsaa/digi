<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-user"></i> Passager: <?php echo htmlspecialchars($data['passenger']['first_name'] . ' ' . $data['passenger']['last_name']); ?>
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/editPassenger/<?php echo $data['passenger']['id']; ?>"
               class="btn btn-warning">
                <i class="fas fa-edit"></i> Éditer
            </a>
            <a href="<?php echo APP_URL; ?>/passenger_transport/passengers" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Passenger Info -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-id-card"></i> Informations Personnelles</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($data['passenger']['first_name'] . ' ' . $data['passenger']['last_name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($data['passenger']['customer_code']); ?></p>

                    <hr>

                    <table class="table table-sm table-borderless text-left">
                        <tr>
                            <th width="40%"><i class="fas fa-phone text-success"></i> Téléphone:</th>
                            <td><a href="tel:<?php echo $data['passenger']['phone']; ?>"><?php echo htmlspecialchars($data['passenger']['phone']); ?></a></td>
                        </tr>
                        <?php if ($data['passenger']['email']): ?>
                            <tr>
                                <th><i class="fas fa-envelope text-info"></i> Email:</th>
                                <td><a href="mailto:<?php echo $data['passenger']['email']; ?>"><?php echo htmlspecialchars($data['passenger']['email']); ?></a></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($data['passenger']['id_card_number']): ?>
                            <tr>
                                <th><i class="fas fa-id-card text-warning"></i> CIN:</th>
                                <td><?php echo htmlspecialchars($data['passenger']['id_card_number']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($data['passenger']['date_of_birth']): ?>
                            <tr>
                                <th><i class="fas fa-birthday-cake text-danger"></i> Naissance:</th>
                                <td><?php echo date('d/m/Y', strtotime($data['passenger']['date_of_birth'])); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($data['passenger']['gender']): ?>
                            <tr>
                                <th><i class="fas fa-venus-mars"></i> Genre:</th>
                                <td>
                                    <?php
                                    $genders = ['male' => 'Homme', 'female' => 'Femme', 'other' => 'Autre'];
                                    echo $genders[$data['passenger']['gender']] ?? $data['passenger']['gender'];
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>

                    <hr>

                    <?php if ($data['passenger']['rating']): ?>
                        <div class="mb-3">
                            <h6>Note Moyenne</h6>
                            <?php
                            $rating = floatval($data['passenger']['rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star text-warning"></i>';
                                } elseif ($i - 0.5 <= $rating) {
                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                } else {
                                    echo '<i class="far fa-star text-warning"></i>';
                                }
                            }
                            ?>
                            <br>
                            <strong class="text-primary"><?php echo number_format($rating, 1); ?>/5</strong>
                        </div>
                    <?php endif; ?>

                    <p class="mb-1">
                        <strong>Membre depuis:</strong><br>
                        <?php echo date('d/m/Y', strtotime($data['passenger']['created_at'])); ?>
                    </p>
                </div>
            </div>

            <!-- Address Card -->
            <?php if ($data['passenger']['address'] || $data['passenger']['city']): ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0"><i class="fas fa-map-marker-alt"></i> Adresse</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($data['passenger']['address']): ?>
                            <p><?php echo nl2br(htmlspecialchars($data['passenger']['address'])); ?></p>
                        <?php endif; ?>
                        <?php if ($data['passenger']['city']): ?>
                            <p class="mb-0">
                                <i class="fas fa-city"></i>
                                <?php echo htmlspecialchars($data['passenger']['city']); ?>
                                <?php if ($data['passenger']['postal_code']): ?>
                                    - <?php echo htmlspecialchars($data['passenger']['postal_code']); ?>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Preferences -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-cog"></i> Préférences</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <strong>Paiement Préféré:</strong><br>
                        <?php
                        $paymentMethods = [
                            'cash' => 'Espèces',
                            'card' => 'Carte Bancaire',
                            'mobile_money' => 'Mobile Money',
                            'account' => 'Compte Client'
                        ];
                        echo $paymentMethods[$data['passenger']['preferred_payment_method']] ?? $data['passenger']['preferred_payment_method'];
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Stats & History -->
        <div class="col-md-8">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow border-left-primary">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Courses
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $data['passenger']['total_trips']; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-route fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow border-left-warning">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Points Fidélité
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $data['passenger']['loyalty_points']; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-star fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow border-left-success">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Statut
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php if ($data['passenger']['is_active']): ?>
                                            <span class="badge badge-success p-2">Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary p-2">Inactif</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trip History -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Historique des Courses
                        (<?php echo count($data['bookings']); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['bookings'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tripsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>N° Réservation</th>
                                        <th>Type</th>
                                        <th>Trajet</th>
                                        <th>Chauffeur</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['bookings'] as $booking): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></td>
                                            <td>
                                                <small><strong><?php echo htmlspecialchars($booking['booking_number']); ?></strong></small>
                                            </td>
                                            <td>
                                                <?php
                                                $typeIcons = [
                                                    'taxi' => '<i class="fas fa-taxi text-warning"></i>',
                                                    'bus' => '<i class="fas fa-bus text-primary"></i>',
                                                    'shuttle' => '<i class="fas fa-shuttle-van text-info"></i>'
                                                ];
                                                echo $typeIcons[$booking['booking_type']] ?? '';
                                                ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-circle text-success"></i>
                                                    <?php echo htmlspecialchars(substr($booking['pickup_address'], 0, 20)); ?>...<br>
                                                    <i class="fas fa-circle text-danger"></i>
                                                    <?php echo htmlspecialchars(substr($booking['dropoff_address'], 0, 20)); ?>...
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($booking['driver_first_name']): ?>
                                                    <small><?php echo htmlspecialchars($booking['driver_first_name']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo number_format($booking['final_amount'], 2); ?> TND</strong></td>
                                            <td>
                                                <?php
                                                $statusClasses = [
                                                    'pending' => 'secondary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    'in_progress' => 'warning'
                                                ];
                                                $statusTexts = [
                                                    'pending' => 'Attente',
                                                    'completed' => 'Complété',
                                                    'cancelled' => 'Annulé',
                                                    'in_progress' => 'En cours'
                                                ];
                                                $badgeClass = $statusClasses[$booking['status']] ?? 'secondary';
                                                $statusText = $statusTexts[$booking['status']] ?? $booking['status'];
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/passenger_transport/viewBooking/<?php echo $booking['id']; ?>"
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Aucune course effectuée pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tripsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 10
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
