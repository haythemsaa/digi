<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-chart-bar"></i> Rapports & Statistiques</h1>
            <p class="text-muted">Analyse des performances des missions</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/missions" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow border-left-primary">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Missions (30j)
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo $data['stats']['total_missions'] ?? 0; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-success">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Missions Terminées
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo $data['stats']['completed_count'] ?? 0; ?>
                    </div>
                    <?php
                    $total = $data['stats']['total_missions'] ?? 1;
                    $completed = $data['stats']['completed_count'] ?? 0;
                    $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;
                    ?>
                    <small class="text-success">
                        <?php echo number_format($completionRate, 1); ?>% taux de complétion
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-info">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Revenu Total
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo number_format($data['stats']['total_revenue'] ?? 0, 2); ?> TND
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-warning">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Paiements en Attente
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo number_format($data['stats']['pending_revenue'] ?? 0, 2); ?> TND
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Répartition par Statut
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Statut</th>
                                    <th>Nombre</th>
                                    <th>Pourcentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = $data['stats']['total_missions'] ?? 1;
                                $statuses = [
                                    ['label' => 'En attente', 'count' => $data['stats']['pending_count'] ?? 0, 'badge' => 'warning'],
                                    ['label' => 'Assignées', 'count' => $data['stats']['assigned_count'] ?? 0, 'badge' => 'info'],
                                    ['label' => 'En cours', 'count' => $data['stats']['in_progress_count'] ?? 0, 'badge' => 'primary'],
                                    ['label' => 'Terminées', 'count' => $data['stats']['completed_count'] ?? 0, 'badge' => 'success']
                                ];

                                foreach ($statuses as $status):
                                    $percentage = $total > 0 ? ($status['count'] / $total) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-<?php echo $status['badge']; ?>">
                                                <?php echo $status['label']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $status['count']; ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-<?php echo $status['badge']; ?>"
                                                     role="progressbar"
                                                     style="width: <?php echo $percentage; ?>%">
                                                    <?php echo number_format($percentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-dollar-sign"></i> Situation Financière
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="mb-3">Revenus (30 derniers jours)</h5>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Revenu Total</span>
                            <strong><?php echo number_format($data['stats']['total_revenue'] ?? 0, 2); ?> TND</strong>
                        </div>
                        <div class="progress mb-3" style="height: 25px;">
                            <?php
                            $totalRevenue = $data['stats']['total_revenue'] ?? 1;
                            $paidRevenue = $data['stats']['paid_revenue'] ?? 0;
                            $pendingRevenue = $data['stats']['pending_revenue'] ?? 0;
                            $paidPercentage = $totalRevenue > 0 ? ($paidRevenue / $totalRevenue) * 100 : 0;
                            $pendingPercentage = $totalRevenue > 0 ? ($pendingRevenue / $totalRevenue) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: <?php echo $paidPercentage; ?>%"
                                 title="Payé: <?php echo number_format($paidRevenue, 2); ?> TND">
                                <?php echo number_format($paidPercentage, 1); ?>%
                            </div>
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: <?php echo $pendingPercentage; ?>%"
                                 title="En attente: <?php echo number_format($pendingRevenue, 2); ?> TND">
                                <?php echo number_format($pendingPercentage, 1); ?>%
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6>Payé</h6>
                                    <h4><?php echo number_format($paidRevenue, 2); ?> TND</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h6>En Attente</h6>
                                    <h4><?php echo number_format($pendingRevenue, 2); ?> TND</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-tachometer-alt"></i> Indicateurs de Performance
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center mb-3">
                    <h6 class="text-muted">Taux de Complétion</h6>
                    <div class="progress mx-auto" style="width: 150px; height: 150px; border-radius: 50%; position: relative;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1;">
                            <h2 class="mb-0"><?php echo number_format($completionRate, 1); ?>%</h2>
                        </div>
                    </div>
                    <p class="text-muted mt-3">
                        <?php echo $data['stats']['completed_count'] ?? 0; ?> sur
                        <?php echo $data['stats']['total_missions'] ?? 0; ?> missions
                    </p>
                </div>

                <div class="col-md-4 text-center mb-3">
                    <h6 class="text-muted">Revenu Moyen/Mission</h6>
                    <?php
                    $avgRevenue = ($data['stats']['completed_count'] ?? 0) > 0
                        ? ($data['stats']['total_revenue'] ?? 0) / ($data['stats']['completed_count'])
                        : 0;
                    ?>
                    <h1 class="text-primary"><?php echo number_format($avgRevenue, 2); ?></h1>
                    <p class="text-muted">TND</p>
                </div>

                <div class="col-md-4 text-center mb-3">
                    <h6 class="text-muted">Missions Aujourd'hui</h6>
                    <h1 class="text-success"><?php echo $data['stats']['completed_today'] ?? 0; ?></h1>
                    <p class="text-muted">Terminées</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="card shadow mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-secondary">
                <i class="fas fa-download"></i> Exporter les Rapports
            </h6>
        </div>
        <div class="card-body">
            <p>Exporter les données de missions pour analyse approfondie.</p>
            <button class="btn btn-success" disabled>
                <i class="fas fa-file-excel"></i> Exporter en Excel
            </button>
            <button class="btn btn-danger" disabled>
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </button>
            <small class="text-muted ml-2">(Fonctionnalité à venir)</small>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
