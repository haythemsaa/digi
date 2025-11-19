<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-bus"></i> Lignes de Bus</h1>
            <p class="text-muted">Gestion des itinéraires et horaires de bus</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/createBusRoute" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Ligne
            </a>
            <a href="<?php echo APP_URL; ?>/passenger_transport/busStops" class="btn btn-success">
                <i class="fas fa-map-marked-alt"></i> Arrêts de Bus
            </a>
        </div>
    </div>

    <!-- Bus Routes Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Toutes les Lignes (<?php echo count($data['routes']); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['routes'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="routesTable">
                                <thead>
                                    <tr>
                                        <th>N° Ligne</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Départ - Arrivée</th>
                                        <th>Distance</th>
                                        <th>Durée</th>
                                        <th>Tarif Base</th>
                                        <th>Horaires</th>
                                        <th>Fréquence</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['routes'] as $route): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-primary p-2" style="font-size: 1.1em; background-color: <?php echo $route['color_code'] ?? '#007bff'; ?>">
                                                    <?php echo htmlspecialchars($route['route_number']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($route['route_name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $typeIcons = [
                                                    'urban' => '<i class="fas fa-city text-primary"></i> Urbain',
                                                    'suburban' => '<i class="fas fa-home text-success"></i> Banlieue',
                                                    'intercity' => '<i class="fas fa-road text-warning"></i> Intercité',
                                                    'express' => '<i class="fas fa-bolt text-danger"></i> Express',
                                                    'shuttle' => '<i class="fas fa-shuttle-van text-info"></i> Navette'
                                                ];
                                                echo $typeIcons[$route['route_type']] ?? $route['route_type'];
                                                ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-circle text-success"></i>
                                                    <?php echo htmlspecialchars($route['start_point']); ?>
                                                    <br>
                                                    <i class="fas fa-circle text-danger"></i>
                                                    <?php echo htmlspecialchars($route['end_point']); ?>
                                                    <?php if ($route['is_circular']): ?>
                                                        <span class="badge badge-info ml-1">Circulaire</span>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td><?php echo number_format($route['total_distance_km'], 1); ?> km</td>
                                            <td><?php echo $route['estimated_duration_minutes']; ?> min</td>
                                            <td><?php echo number_format($route['base_fare'], 2); ?> TND</td>
                                            <td>
                                                <small>
                                                    Premier: <?php echo substr($route['first_departure'], 0, 5); ?><br>
                                                    Dernier: <?php echo substr($route['last_departure'], 0, 5); ?>
                                                </small>
                                            </td>
                                            <td><?php echo $route['frequency_minutes']; ?> min</td>
                                            <td>
                                                <?php
                                                $statusClasses = [
                                                    'active' => 'success',
                                                    'seasonal' => 'info',
                                                    'suspended' => 'warning',
                                                    'discontinued' => 'danger'
                                                ];
                                                $statusTexts = [
                                                    'active' => 'Actif',
                                                    'seasonal' => 'Saisonnier',
                                                    'suspended' => 'Suspendu',
                                                    'discontinued' => 'Discontinué'
                                                ];
                                                $badgeClass = $statusClasses[$route['status']] ?? 'secondary';
                                                $statusText = $statusTexts[$route['status']] ?? $route['status'];
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/passenger_transport/viewBusRoute/<?php echo $route['id']; ?>"
                                                   class="btn btn-sm btn-info" title="Voir détails">
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
                            <i class="fas fa-info-circle"></i> Aucune ligne de bus enregistrée.
                            <a href="<?php echo APP_URL; ?>/passenger_transport/createBusRoute" class="alert-link">
                                Créer la première ligne
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#routesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "asc"]],
        "pageLength": 25
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
