<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3"><i class="fas fa-brain text-primary"></i> Smart Delivery AI</h1>
            <p class="text-muted">Système de livraison intelligent avec optimisation IA</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Colis en attente</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['stats']['pending_packages']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Routes actives</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['stats']['active_routes']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-route fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Livraisons aujourd'hui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['stats']['today_deliveries']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Efficacité moyenne</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($data['stats']['avg_route_efficiency'], 1); ?>%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bolt"></i> Actions rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo APP_URL; ?>/smart_delivery/addPackage" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Nouveau colis
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo APP_URL; ?>/smart_delivery/createRoute" class="btn btn-success btn-block">
                                <i class="fas fa-route"></i> Nouvelle route
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo APP_URL; ?>/smart_delivery/packages" class="btn btn-info btn-block">
                                <i class="fas fa-box"></i> Voir colis
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo APP_URL; ?>/smart_delivery/routes" class="btn btn-warning btn-block">
                                <i class="fas fa-list"></i> Voir routes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Routes -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> Routes récentes</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['recent_routes'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Véhicule</th>
                                        <th>Chauffeur</th>
                                        <th>Colis</th>
                                        <th>Distance</th>
                                        <th>Efficacité</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['recent_routes'] as $route): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($route['route_number']); ?></strong></td>
                                            <td><?php echo date('d/m/Y', strtotime($route['route_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($route['registration_number']); ?></td>
                                            <td><?php echo htmlspecialchars($route['driver_name'] ?? 'Non assigné'); ?></td>
                                            <td><?php echo $route['total_packages']; ?></td>
                                            <td><?php echo number_format($route['total_distance'], 1); ?> km</td>
                                            <td>
                                                <?php if ($route['route_efficiency']): ?>
                                                    <span class="badge badge-success"><?php echo number_format($route['route_efficiency'], 1); ?>%</span>
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
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucune route récente. Créez votre première route optimisée par IA !
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
