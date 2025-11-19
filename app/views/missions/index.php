<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-tasks"></i> Gestion des Missions</h1>
            <p class="text-muted">Tableau de bord missions et livraisons</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/missions/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Mission
            </a>
            <a href="<?php echo APP_URL; ?>/missions/calendar" class="btn btn-info">
                <i class="fas fa-calendar"></i> Planning
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['pending_count'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Assignées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['assigned_count'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                En Cours
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['in_progress_count'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Terminées (Aujourd'hui)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['completed_today'] ?? 0; ?>
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

    <!-- Revenue Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h6 class="text-primary text-uppercase mb-2">Revenu Total (30j)</h6>
                    <h3 class="mb-0"><?php echo number_format($data['stats']['total_revenue'] ?? 0, 2); ?> TND</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h6 class="text-success text-uppercase mb-2">Payé</h6>
                    <h3 class="mb-0"><?php echo number_format($data['stats']['paid_revenue'] ?? 0, 2); ?> TND</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h6 class="text-warning text-uppercase mb-2">En Attente</h6>
                    <h3 class="mb-0"><?php echo number_format($data['stats']['pending_revenue'] ?? 0, 2); ?> TND</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upcoming Missions -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> Missions à Venir (7 jours)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['upcoming_missions'])): ?>
                        <div class="list-group">
                            <?php foreach ($data['upcoming_missions'] as $mission): ?>
                                <a href="<?php echo APP_URL; ?>/missions/view/<?php echo $mission['id']; ?>"
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($mission['mission_number']); ?>
                                            -
                                            <?php echo htmlspecialchars($mission['client_name']); ?>
                                        </h6>
                                        <small>
                                            <?php
                                            $priorityBadges = [
                                                'low' => 'secondary',
                                                'normal' => 'info',
                                                'high' => 'warning',
                                                'urgent' => 'danger'
                                            ];
                                            $badge = $priorityBadges[$mission['priority']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $badge; ?>">
                                                <?php echo strtoupper($mission['priority']); ?>
                                            </span>
                                        </small>
                                    </div>
                                    <p class="mb-1">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($mission['scheduled_start'])); ?>
                                    </p>
                                    <small>
                                        <?php if ($mission['registration_number']): ?>
                                            <i class="fas fa-car"></i> <?php echo htmlspecialchars($mission['registration_number']); ?>
                                        <?php endif; ?>
                                        <?php if ($mission['driver_name']): ?>
                                            | <i class="fas fa-user"></i> <?php echo htmlspecialchars($mission['driver_name']); ?>
                                        <?php endif; ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucune mission planifiée.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Missions -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-history"></i> Missions Récentes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['recent_missions'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Client</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['recent_missions'] as $mission): ?>
                                        <tr onclick="window.location='<?php echo APP_URL; ?>/missions/view/<?php echo $mission['id']; ?>'"
                                            style="cursor: pointer;">
                                            <td>
                                                <small><?php echo htmlspecialchars($mission['mission_number']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($mission['client_name']); ?></td>
                                            <td>
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
                                                $badge = $statusBadges[$mission['status']] ?? 'secondary';
                                                $label = $statusLabels[$mission['status']] ?? $mission['status'];
                                                ?>
                                                <span class="badge badge-<?php echo $badge; ?>">
                                                    <?php echo $label; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y', strtotime($mission['created_at'])); ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="<?php echo APP_URL; ?>/missions/all" class="btn btn-primary btn-sm">
                            Voir Toutes les Missions
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Aucune mission enregistrée.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
