<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0"><i class="fas fa-taxi"></i> Transport de Voyageurs</h1>
            <p class="text-muted">Gestion des taxis et bus - Tableau de bord</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow border-left-primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Réservations Aujourd'hui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['today_bookings']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow border-left-warning h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Courses en Cours
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['active_rides']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-route fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow border-left-success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Chauffeurs Disponibles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['available_drivers']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow border-left-info h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Revenu Aujourd'hui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['stats']['today_revenue'], 2); ?> TND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary text-white">
                    <h6 class="m-0"><i class="fas fa-bolt"></i> Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo APP_URL; ?>/passenger_transport/createBooking" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Nouvelle Réservation
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo APP_URL; ?>/passenger_transport/addPassenger" class="btn btn-success btn-block">
                                <i class="fas fa-user-plus"></i> Ajouter Passager
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo APP_URL; ?>/passenger_transport/bookings" class="btn btn-info btn-block">
                                <i class="fas fa-list"></i> Toutes les Réservations
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo APP_URL; ?>/passenger_transport/analytics" class="btn btn-warning btn-block">
                                <i class="fas fa-chart-bar"></i> Analytiques
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Réservations Récentes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['recent_bookings'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Réservation</th>
                                        <th>Type</th>
                                        <th>Passager</th>
                                        <th>Départ</th>
                                        <th>Arrivée</th>
                                        <th>Chauffeur</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['recent_bookings'] as $booking): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['booking_number']); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $typeIcons = [
                                                    'taxi' => '<i class="fas fa-taxi text-warning"></i> Taxi',
                                                    'bus' => '<i class="fas fa-bus text-primary"></i> Bus',
                                                    'shuttle' => '<i class="fas fa-shuttle-van text-info"></i> Navette'
                                                ];
                                                echo $typeIcons[$booking['booking_type']] ?? $booking['booking_type'];
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($booking['passenger_first_name'] . ' ' . $booking['passenger_last_name']); ?>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($booking['pickup_address'], 0, 30)); ?>...</small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($booking['dropoff_address'], 0, 30)); ?>...</small>
                                            </td>
                                            <td>
                                                <?php if ($booking['driver_first_name']): ?>
                                                    <?php echo htmlspecialchars($booking['driver_first_name'] . ' ' . $booking['driver_last_name']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Non assigné</span>
                                                <?php endif; ?>
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
                                                    'pending' => 'En Attente',
                                                    'confirmed' => 'Confirmé',
                                                    'driver_assigned' => 'Chauffeur Assigné',
                                                    'driver_arrived' => 'Chauffeur Arrivé',
                                                    'in_progress' => 'En Cours',
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
                            <i class="fas fa-info-circle"></i> Aucune réservation récente.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
