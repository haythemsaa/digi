<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-list"></i> Réservations</h1>
            <p class="text-muted">Gestion de toutes les courses et réservations</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/createBooking" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Réservation
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-filter"></i> Filtres</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'pending') ? 'selected' : ''; ?>>En Attente</option>
                                        <option value="confirmed" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmé</option>
                                        <option value="driver_assigned" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'driver_assigned') ? 'selected' : ''; ?>>Chauffeur Assigné</option>
                                        <option value="in_progress" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'in_progress') ? 'selected' : ''; ?>>En Cours</option>
                                        <option value="completed" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'completed') ? 'selected' : ''; ?>>Complété</option>
                                        <option value="cancelled" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'cancelled') ? 'selected' : ''; ?>>Annulé</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="booking_type">Type</label>
                                    <select class="form-control" id="booking_type" name="booking_type">
                                        <option value="">Tous les types</option>
                                        <option value="taxi" <?php echo (isset($data['filters']['booking_type']) && $data['filters']['booking_type'] == 'taxi') ? 'selected' : ''; ?>>Taxi</option>
                                        <option value="bus" <?php echo (isset($data['filters']['booking_type']) && $data['filters']['booking_type'] == 'bus') ? 'selected' : ''; ?>>Bus</option>
                                        <option value="shuttle" <?php echo (isset($data['filters']['booking_type']) && $data['filters']['booking_type'] == 'shuttle') ? 'selected' : ''; ?>>Navette</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_from">Date Début</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from"
                                           value="<?php echo $data['filters']['date_from'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_to">Date Fin</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to"
                                           value="<?php echo $data['filters']['date_to'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        <a href="<?php echo APP_URL; ?>/passenger_transport/bookings" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Liste des Réservations (<?php echo count($data['bookings']); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['bookings'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="bookingsTable">
                                <thead>
                                    <tr>
                                        <th>N° Réservation</th>
                                        <th>Type</th>
                                        <th>Passager</th>
                                        <th>Départ</th>
                                        <th>Arrivée</th>
                                        <th>Chauffeur</th>
                                        <th>Distance</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Paiement</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['bookings'] as $booking): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['booking_number']); ?></strong>
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
                                                <?php echo htmlspecialchars($booking['passenger_first_name'] . ' ' . $booking['passenger_last_name']); ?>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($booking['pickup_address'], 0, 25)); ?>...</small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($booking['dropoff_address'], 0, 25)); ?>...</small>
                                            </td>
                                            <td>
                                                <?php if ($booking['driver_first_name']): ?>
                                                    <small><?php echo htmlspecialchars($booking['driver_first_name'] . ' ' . $booking['driver_last_name']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $booking['estimated_distance_km'] ? number_format($booking['estimated_distance_km'], 1) . ' km' : '-'; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($booking['final_amount'], 2); ?> TND</strong>
                                            </td>
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
                                                    'pending' => 'Attente',
                                                    'confirmed' => 'Confirmé',
                                                    'driver_assigned' => 'Assigné',
                                                    'driver_arrived' => 'Arrivé',
                                                    'in_progress' => 'En cours',
                                                    'completed' => 'Complété',
                                                    'cancelled' => 'Annulé',
                                                    'no_show' => 'Absent'
                                                ];
                                                $badgeClass = $statusClasses[$booking['status']] ?? 'secondary';
                                                $statusText = $statusTexts[$booking['status']] ?? $booking['status'];
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td>
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
                                                $paymentBadge = $paymentClasses[$booking['payment_status']] ?? 'secondary';
                                                $paymentText = $paymentTexts[$booking['payment_status']] ?? $booking['payment_status'];
                                                ?>
                                                <span class="badge badge-<?php echo $paymentBadge; ?>">
                                                    <?php echo $paymentText; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/passenger_transport/viewBooking/<?php echo $booking['id']; ?>"
                                                   class="btn btn-sm btn-info"
                                                   title="Voir détails">
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
                            <i class="fas fa-info-circle"></i> Aucune réservation trouvée avec ces critères.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[10, "desc"]],
        "pageLength": 25
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
