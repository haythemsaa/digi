<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0"><i class="fas fa-chart-line"></i> Analytiques & Performance IA</h1>
            <p class="text-muted">Tableau de bord des performances de livraison et optimisations IA</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Taux de Réussite Moyen
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['kpis']['avg_success_rate'], 1); ?>%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
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
                                Efficacité Moyenne IA
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['kpis']['avg_efficiency'], 1); ?>%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-brain fa-2x text-primary"></i>
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
                                Distance Économisée (30j)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['kpis']['total_distance_saved'], 1); ?> km
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-route fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Économie Carburant (30j)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['kpis']['total_fuel_saved'], 2); ?> TND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gas-pump fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Delivery Success Rate Trend -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Tendance du Taux de Réussite (30 derniers jours)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="successRateChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Route Efficiency Distribution -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-pie"></i> Répartition Efficacité
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="efficiencyPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-table"></i> Historique des Performances (30 derniers jours)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['performance'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="performanceTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Route</th>
                                        <th>Véhicule</th>
                                        <th>Colis</th>
                                        <th>Distance Planifiée</th>
                                        <th>Distance Réelle</th>
                                        <th>Économie</th>
                                        <th>Taux de Réussite</th>
                                        <th>Efficacité Route</th>
                                        <th>Efficacité Chargement</th>
                                        <th>Coût Carburant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['performance'] as $perf): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($perf['date'])); ?></td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/smart_delivery/viewRoute/<?php echo $perf['route_id']; ?>">
                                                    <?php echo htmlspecialchars($perf['route_number']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($perf['vehicle_registration']); ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    <?php echo $perf['delivered_packages']; ?>/<?php echo $perf['total_packages']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($perf['planned_distance'], 1); ?> km</td>
                                            <td><?php echo number_format($perf['actual_distance'], 1); ?> km</td>
                                            <td class="text-success">
                                                <strong>
                                                    <?php
                                                    $saving = $perf['planned_distance'] - $perf['actual_distance'];
                                                    echo number_format($saving, 1);
                                                    ?> km
                                                </strong>
                                            </td>
                                            <td>
                                                <?php
                                                $successRate = floatval($perf['delivery_success_rate']);
                                                $badgeClass = $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo number_format($successRate, 1); ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $routeEff = floatval($perf['route_efficiency']);
                                                $badgeClass = $routeEff >= 80 ? 'success' : ($routeEff >= 60 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo number_format($routeEff, 1); ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $loadEff = floatval($perf['loading_efficiency']);
                                                $badgeClass = $loadEff >= 80 ? 'success' : ($loadEff >= 60 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo number_format($loadEff, 1); ?>%
                                                </span>
                                            </td>
                                            <td><?php echo number_format($perf['fuel_cost'], 2); ?> TND</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Aucune donnée de performance disponible pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Optimization Insights -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow border-left-primary">
                <div class="card-header bg-gradient-primary text-white">
                    <h6 class="m-0"><i class="fas fa-lightbulb"></i> Insights IA - Routes</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">Optimisation de Trajet (VRP)</h6>
                    <ul class="mb-3">
                        <li>Algorithme génétique avec <?php echo $data['kpis']['genetic_generations'] ?? 100; ?> générations</li>
                        <li>Amélioration moyenne: <strong><?php echo number_format($data['kpis']['avg_efficiency'], 1); ?>%</strong></li>
                        <li>Distance économisée: <strong><?php echo number_format($data['kpis']['total_distance_saved'], 1); ?> km</strong></li>
                    </ul>

                    <h6 class="text-success">Recommandations</h6>
                    <ul class="mb-0">
                        <li>Grouper les livraisons par zone géographique</li>
                        <li>Respecter les créneaux horaires pour éviter les retours</li>
                        <li>Utiliser des véhicules adaptés à la charge</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow border-left-success">
                <div class="card-header bg-gradient-success text-white">
                    <h6 class="m-0"><i class="fas fa-cube"></i> Insights IA - Chargement</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-success">Bin Packing 3D</h6>
                    <ul class="mb-3">
                        <li>Utilisation moyenne de l'espace: <strong><?php echo number_format($data['kpis']['avg_space_utilization'] ?? 75, 1); ?>%</strong></li>
                        <li>Respect des contraintes: LIFO, fragilité, empilement</li>
                        <li>Réduction du nombre de voyages nécessaires</li>
                    </ul>

                    <h6 class="text-warning">Recommandations</h6>
                    <ul class="mb-0">
                        <li>Charger les articles fragiles en dernier</li>
                        <li>Respecter l'ordre LIFO pour faciliter le déchargement</li>
                        <li>Équilibrer le poids pour la stabilité</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Prepare data for charts
const performanceData = <?php echo json_encode($data['performance']); ?>;

// Success Rate Trend Chart
const successRateCtx = document.getElementById('successRateChart').getContext('2d');
new Chart(successRateCtx, {
    type: 'line',
    data: {
        labels: performanceData.map(p => {
            const date = new Date(p.date);
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
        }),
        datasets: [{
            label: 'Taux de Réussite (%)',
            data: performanceData.map(p => parseFloat(p.delivery_success_rate)),
            borderColor: 'rgb(28, 200, 138)',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Efficacité Route (%)',
            data: performanceData.map(p => parseFloat(p.route_efficiency)),
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});

// Efficiency Distribution Pie Chart
const efficiencyCtx = document.getElementById('efficiencyPieChart').getContext('2d');

// Calculate distribution
let excellent = 0, good = 0, average = 0, poor = 0;
performanceData.forEach(p => {
    const eff = parseFloat(p.route_efficiency);
    if (eff >= 90) excellent++;
    else if (eff >= 75) good++;
    else if (eff >= 60) average++;
    else poor++;
});

new Chart(efficiencyCtx, {
    type: 'doughnut',
    data: {
        labels: ['Excellent (≥90%)', 'Bon (75-89%)', 'Moyen (60-74%)', 'Faible (<60%)'],
        datasets: [{
            data: [excellent, good, average, poor],
            backgroundColor: [
                'rgb(28, 200, 138)',
                'rgb(54, 185, 204)',
                'rgb(246, 194, 62)',
                'rgb(231, 74, 59)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// DataTables initialization
$(document).ready(function() {
    $('#performanceTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 25
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
