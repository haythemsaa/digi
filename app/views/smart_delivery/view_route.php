<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-route"></i> <?php echo htmlspecialchars($data['route']['route_number']); ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/smart_delivery">Smart Delivery</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/smart_delivery/routes">Routes</a></li>
                    <li class="breadcrumb-item active"><?php echo $data['route']['route_number']; ?></li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-right">
            <?php if ($data['route']['status'] == 'draft' || $data['route']['status'] == 'optimized'): ?>
                <a href="<?php echo APP_URL; ?>/smart_delivery/editRoute/<?php echo $data['route']['id']; ?>"
                   class="btn btn-primary">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Route Info Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-info-circle"></i> Informations de la Route</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($data['route']['route_date'])); ?></p>
                            <p><strong>Véhicule :</strong> <?php echo htmlspecialchars($data['route']['registration_number']); ?></p>
                            <p><strong>Chauffeur :</strong> <?php echo htmlspecialchars($data['route']['driver_name'] ?? 'Non assigné'); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Nombre de colis :</strong> <?php echo count($data['route']['stops']); ?></p>
                            <p><strong>Poids total :</strong> <?php echo number_format($data['route']['total_weight'], 2); ?> kg</p>
                            <p><strong>Volume total :</strong> <?php echo number_format($data['route']['total_volume'], 3); ?> m³</p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Distance :</strong> <?php echo number_format($data['route']['total_distance'], 2); ?> km</p>
                            <p><strong>Durée estimée :</strong> <?php echo floor($data['route']['estimated_duration'] / 60); ?>h<?php echo $data['route']['estimated_duration'] % 60; ?>m</p>
                            <p><strong>Efficacité route :</strong>
                                <?php if ($data['route']['route_efficiency']): ?>
                                    <span class="badge badge-success"><?php echo number_format($data['route']['route_efficiency'], 1); ?>%</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">N/A</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Statut :</strong>
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
                                $badgeClass = $statusClasses[$data['route']['status']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?php echo $badgeClass; ?> badge-lg">
                                    <?php echo ucfirst($data['route']['status']); ?>
                                </span>
                            </p>
                            <p><strong>Score optimisation :</strong>
                                <?php if ($data['route']['optimization_score']): ?>
                                    <span class="badge badge-success"><?php echo number_format($data['route']['optimization_score'], 1); ?>/100</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">N/A</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Chargement :</strong>
                                <?php if ($data['route']['loading_efficiency']): ?>
                                    <span class="badge badge-info"><?php echo number_format($data['route']['loading_efficiency'], 1); ?>%</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">N/A</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Optimization Actions -->
    <?php if ($data['route']['status'] == 'draft' || $data['route']['status'] == 'optimized'): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow border-left-primary">
                    <div class="card-header bg-gradient-primary text-white">
                        <h6 class="m-0 font-weight-bold"><i class="fas fa-brain"></i> Optimisation Intelligente par IA</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h5 class="text-primary"><i class="fas fa-route"></i></h5>
                                        <h6>Optimiser la Tournée</h6>
                                        <p class="small text-muted">Calcule le meilleur ordre des arrêts pour minimiser la distance</p>
                                        <form method="POST" action="<?php echo APP_URL; ?>/smart_delivery/optimizeRoute/<?php echo $data['route']['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-brain"></i> Optimiser Route
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h5 class="text-success"><i class="fas fa-cube"></i></h5>
                                        <h6>Optimiser le Chargement</h6>
                                        <p class="small text-muted">Calcule le meilleur placement 3D des colis dans le véhicule</p>
                                        <form method="POST" action="<?php echo APP_URL; ?>/smart_delivery/optimizeLoading/<?php echo $data['route']['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-cube"></i> Optimiser Chargement
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h5 class="text-warning"><i class="fas fa-bolt"></i></h5>
                                        <h6>Optimisation Complète</h6>
                                        <p class="small text-muted">Optimise à la fois la route ET le chargement en un clic</p>
                                        <form method="POST" action="<?php echo APP_URL; ?>/smart_delivery/completeOptimization/<?php echo $data['route']['id']; ?>">
                                            <button type="submit" class="btn btn-warning btn-block">
                                                <i class="fas fa-bolt"></i> Optimisation Totale
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stops List -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ol"></i> Liste des Arrêts (<?php echo count($data['route']['stops']); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['route']['stops'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Colis</th>
                                        <th>Adresse</th>
                                        <th>Contact</th>
                                        <th>Poids</th>
                                        <th>Fenêtre horaire</th>
                                        <th>ETA</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['route']['stops'] as $stop): ?>
                                        <tr>
                                            <td class="text-center"><strong><?php echo $stop['stop_sequence']; ?></strong></td>
                                            <td><strong><?php echo htmlspecialchars($stop['package_number']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($stop['description']); ?></small>
                                            </td>
                                            <td><small><?php echo htmlspecialchars($stop['address']); ?></small></td>
                                            <td>
                                                <?php echo htmlspecialchars($stop['delivery_contact']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($stop['delivery_phone']); ?></small>
                                            </td>
                                            <td><?php echo $stop['weight']; ?> kg</td>
                                            <td>
                                                <?php if ($stop['time_window_start'] || $stop['time_window_end']): ?>
                                                    <small>
                                                        <?php echo substr($stop['time_window_start'], 0, 5); ?> -
                                                        <?php echo substr($stop['time_window_end'], 0, 5); ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($stop['estimated_arrival']): ?>
                                                    <small><?php echo date('H:i', strtotime($stop['estimated_arrival'])); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $stopStatusClasses = [
                                                    'pending' => 'secondary',
                                                    'arrived' => 'info',
                                                    'delivered' => 'success',
                                                    'failed' => 'danger',
                                                    'skipped' => 'warning'
                                                ];
                                                $stopBadgeClass = $stopStatusClasses[$stop['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $stopBadgeClass; ?>">
                                                    <?php echo ucfirst($stop['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php if ($stop['distance_from_previous']): ?>
                                            <tr class="table-light">
                                                <td colspan="8" class="text-center small text-muted">
                                                    <i class="fas fa-arrow-down"></i> <?php echo number_format($stop['distance_from_previous'], 2); ?> km
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun arrêt dans cette route.
                            <a href="<?php echo APP_URL; ?>/smart_delivery/editRoute/<?php echo $data['route']['id']; ?>">Ajouter des colis</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Plan -->
    <?php if ($data['route']['loading_plan']): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow border-left-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0 font-weight-bold"><i class="fas fa-cube"></i> Plan de Chargement 3D</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <p><strong>Algorithme :</strong> <?php echo $data['route']['loading_plan']['algorithm_used']; ?></p>
                                <p><strong>Temps calcul :</strong> <?php echo number_format($data['route']['loading_plan']['computation_time'], 2); ?>s</p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Utilisation espace :</strong>
                                    <span class="badge badge-success badge-lg">
                                        <?php echo number_format($data['route']['loading_plan']['space_utilization'], 1); ?>%
                                    </span>
                                </p>
                                <p><strong>Colis placés :</strong>
                                    <?php echo $data['route']['loading_plan']['total_packages_fitted']; ?> /
                                    <?php echo $data['route']['loading_plan']['total_packages_planned']; ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Score distribution :</strong>
                                    <?php echo number_format($data['route']['loading_plan']['weight_distribution_score'], 1); ?>
                                </p>
                                <p><strong>Violations :</strong>
                                    <?php if ($data['route']['loading_plan']['constraint_violations'] == 0): ?>
                                        <span class="badge badge-success">Aucune</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <?php echo $data['route']['loading_plan']['constraint_violations']; ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-3 text-right">
                                <a href="<?php echo APP_URL; ?>/smart_delivery/warehouseInterface/<?php echo $data['route']['id']; ?>"
                                   class="btn btn-success btn-lg">
                                    <i class="fas fa-warehouse"></i> Interface Magasinier
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cogs"></i> Actions</h6>
                </div>
                <div class="card-body">
                    <a href="<?php echo APP_URL; ?>/smart_delivery/exportRoute/<?php echo $data['route']['id']; ?>"
                       class="btn btn-info">
                        <i class="fas fa-file-csv"></i> Exporter en CSV
                    </a>

                    <?php if ($data['route']['loading_plan']): ?>
                        <a href="<?php echo APP_URL; ?>/smart_delivery/loadingPlan/<?php echo $data['route']['id']; ?>"
                           class="btn btn-success">
                            <i class="fas fa-cube"></i> Voir Plan de Chargement
                        </a>
                    <?php endif; ?>

                    <a href="<?php echo APP_URL; ?>/smart_delivery/routes" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour aux routes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
