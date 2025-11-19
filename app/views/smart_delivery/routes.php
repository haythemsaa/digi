<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-route"></i> Routes de Livraison</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/smart_delivery/createRoute" class="btn btn-success">
                <i class="fas fa-plus"></i> Nouvelle route
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <label class="mr-2">Statut:</label>
                        <select name="status" class="form-control mr-2">
                            <option value="">Tous</option>
                            <option value="draft">Brouillon</option>
                            <option value="optimized">Optimisé</option>
                            <option value="assigned">Assigné</option>
                            <option value="loading">En chargement</option>
                            <option value="in_progress">En cours</option>
                            <option value="completed">Terminé</option>
                        </select>

                        <label class="mr-2 ml-3">Date:</label>
                        <input type="date" name="date" class="form-control mr-2"
                               value="<?php echo $_GET['date'] ?? ''; ?>">

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Routes Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <?php if (!empty($data['routes'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="routesTable">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Véhicule</th>
                                        <th>Chauffeur</th>
                                        <th>Colis</th>
                                        <th>Distance</th>
                                        <th>Durée</th>
                                        <th>Efficacité Route</th>
                                        <th>Chargement</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['routes'] as $route): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($route['route_number']); ?></strong></td>
                                            <td><?php echo date('d/m/Y', strtotime($route['route_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($route['registration_number']); ?></td>
                                            <td><?php echo htmlspecialchars($route['driver_name'] ?? 'Non assigné'); ?></td>
                                            <td class="text-center"><?php echo $route['total_packages'] ?? 0; ?></td>
                                            <td><?php echo $route['total_distance'] ? number_format($route['total_distance'], 1) . ' km' : '-'; ?></td>
                                            <td>
                                                <?php if ($route['estimated_duration']): ?>
                                                    <?php echo floor($route['estimated_duration'] / 60); ?>h<?php echo $route['estimated_duration'] % 60; ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($route['route_efficiency']): ?>
                                                    <span class="badge badge-success">
                                                        <?php echo number_format($route['route_efficiency'], 1); ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($route['loading_efficiency']): ?>
                                                    <span class="badge badge-info">
                                                        <?php echo number_format($route['loading_efficiency'], 1); ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClasses = [
                                                    'draft' => 'secondary',
                                                    'optimized' => 'info',
                                                    'assigned' => 'primary',
                                                    'loading' => 'warning',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $badgeClass = $statusClasses[$route['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo ucfirst($route['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/smart_delivery/viewRoute/<?php echo $route['id']; ?>"
                                                   class="btn btn-sm btn-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($route['status'] == 'draft' || $route['status'] == 'optimized'): ?>
                                                    <a href="<?php echo APP_URL; ?>/smart_delivery/editRoute/<?php echo $route['id']; ?>"
                                                       class="btn btn-sm btn-primary" title="Éditer">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucune route trouvée.
                            <a href="<?php echo APP_URL; ?>/smart_delivery/createRoute">Créer une nouvelle route</a>
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
        "order": [[1, "desc"]]
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
