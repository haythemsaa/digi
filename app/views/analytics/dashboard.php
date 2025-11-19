<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?? 'Tableau de Bord Analytique' ?> - Pakiparc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .kpi-card {
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .kpi-card.primary { border-left-color: #0d6efd; }
        .kpi-card.success { border-left-color: #198754; }
        .kpi-card.warning { border-left-color: #ffc107; }
        .kpi-card.danger { border-left-color: #dc3545; }
        .kpi-card.info { border-left-color: #0dcaf0; }
        .kpi-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        .kpi-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .kpi-icon {
            font-size: 2.5rem;
            opacity: 0.3;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        .stat-badge {
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h1><i class="fas fa-chart-line me-2"></i><?= $data['title'] ?></h1>
                <p class="text-muted">Analyse détaillée de votre flotte et opérations</p>
            </div>
            <div class="col-md-6 text-end">
                <!-- Period selector -->
                <div class="btn-group me-2" role="group">
                    <a href="?period=day" class="btn btn-outline-primary <?= $data['period'] === 'day' ? 'active' : '' ?>">Jour</a>
                    <a href="?period=week" class="btn btn-outline-primary <?= $data['period'] === 'week' ? 'active' : '' ?>">Semaine</a>
                    <a href="?period=month" class="btn btn-outline-primary <?= $data['period'] === 'month' ? 'active' : '' ?>">Mois</a>
                    <a href="?period=quarter" class="btn btn-outline-primary <?= $data['period'] === 'quarter' ? 'active' : '' ?>">Trimestre</a>
                    <a href="?period=year" class="btn btn-outline-primary <?= $data['period'] === 'year' ? 'active' : '' ?>">Année</a>
                </div>

                <!-- Export buttons -->
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-1"></i>PDF
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Fleet KPIs -->
        <h4 class="mb-3"><i class="fas fa-car me-2"></i>Indicateurs Flotte</h4>
        <div class="row mb-4">
            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card kpi-card primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Total Véhicules</div>
                                <div class="kpi-value text-primary"><?= $data['kpis']['fleet']['total_vehicles'] ?></div>
                            </div>
                            <i class="fas fa-car kpi-icon text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card kpi-card success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Véhicules Actifs</div>
                                <div class="kpi-value text-success"><?= $data['kpis']['fleet']['active_vehicles'] ?></div>
                            </div>
                            <i class="fas fa-check-circle kpi-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card kpi-card warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">En Maintenance</div>
                                <div class="kpi-value text-warning"><?= $data['kpis']['fleet']['in_maintenance'] ?></div>
                            </div>
                            <i class="fas fa-wrench kpi-icon text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card kpi-card info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Âge Moyen</div>
                                <div class="kpi-value text-info"><?= number_format($data['kpis']['fleet']['avg_age'], 1) ?> <small>ans</small></div>
                            </div>
                            <i class="fas fa-calendar kpi-icon text-info"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card kpi-card primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Taux Utilisation</div>
                                <div class="kpi-value text-primary"><?= number_format($data['kpis']['fleet']['utilization_rate'], 1) ?>%</div>
                            </div>
                            <i class="fas fa-chart-pie kpi-icon text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card kpi-card success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Distance Totale</div>
                                <div class="kpi-value text-success"><?= number_format($data['kpis']['fleet']['total_distance'], 0) ?> <small>km</small></div>
                            </div>
                            <i class="fas fa-road kpi-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial KPIs -->
        <h4 class="mb-3"><i class="fas fa-euro-sign me-2"></i>Indicateurs Financiers</h4>
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Chiffre d'Affaires</div>
                                <div class="kpi-value text-success"><?= number_format($data['kpis']['financial']['total_revenue'], 2) ?> €</div>
                            </div>
                            <i class="fas fa-arrow-up kpi-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Coûts Totaux</div>
                                <div class="kpi-value text-danger"><?= number_format($data['kpis']['financial']['total_costs'], 2) ?> €</div>
                                <small class="text-muted">
                                    Carburant: <?= number_format($data['kpis']['financial']['fuel_costs'], 2) ?> € |
                                    Maintenance: <?= number_format($data['kpis']['financial']['maintenance_costs'], 2) ?> €
                                </small>
                            </div>
                            <i class="fas fa-arrow-down kpi-icon text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card <?= $data['kpis']['financial']['profit'] >= 0 ? 'success' : 'danger' ?> h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Bénéfice</div>
                                <div class="kpi-value <?= $data['kpis']['financial']['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($data['kpis']['financial']['profit'], 2) ?> €
                                </div>
                            </div>
                            <i class="fas fa-<?= $data['kpis']['financial']['profit'] >= 0 ? 'thumbs-up' : 'thumbs-down' ?> kpi-icon <?= $data['kpis']['financial']['profit'] >= 0 ? 'text-success' : 'text-danger' ?>"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Marge Bénéficiaire</div>
                                <div class="kpi-value text-info"><?= number_format($data['kpis']['financial']['profit_margin'], 2) ?>%</div>
                            </div>
                            <i class="fas fa-percentage kpi-icon text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations KPIs -->
        <h4 class="mb-3"><i class="fas fa-tasks me-2"></i>Indicateurs Opérationnels</h4>
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Total Missions</div>
                                <div class="kpi-value text-primary"><?= $data['kpis']['operations']['total_missions'] ?></div>
                            </div>
                            <i class="fas fa-briefcase kpi-icon text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Missions Terminées</div>
                                <div class="kpi-value text-success"><?= $data['kpis']['operations']['completed_missions'] ?></div>
                            </div>
                            <i class="fas fa-check-double kpi-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Taux Complétion</div>
                                <div class="kpi-value text-info"><?= number_format($data['kpis']['operations']['completion_rate'], 1) ?>%</div>
                            </div>
                            <i class="fas fa-chart-line kpi-icon text-info"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Durée Moyenne</div>
                                <div class="kpi-value text-warning"><?= number_format($data['kpis']['operations']['avg_duration'], 0) ?> <small>min</small></div>
                            </div>
                            <i class="fas fa-clock kpi-icon text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Évolution des Coûts</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="costsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution des Missions</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="missionsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-gas-pump me-2"></i>Consommation Carburant</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="fuelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-wrench me-2"></i>Maintenance Préventive vs Curative</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="maintenanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Tables -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 5 Véhicules</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Véhicule</th>
                                        <th>Missions</th>
                                        <th>Distance (km)</th>
                                        <th>Coût/km (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['top_vehicles'] as $vehicle): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($vehicle['registration_number']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?></small>
                                        </td>
                                        <td><?= $vehicle['total_missions'] ?></td>
                                        <td><?= number_format($vehicle['total_distance'], 0) ?></td>
                                        <td><?= number_format($vehicle['cost_per_km'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top 5 Conducteurs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Conducteur</th>
                                        <th>Missions</th>
                                        <th>Distance (km)</th>
                                        <th>Infractions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['top_drivers'] as $driver): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></strong>
                                        </td>
                                        <td><?= $driver['total_missions'] ?></td>
                                        <td><?= number_format($driver['total_distance'], 0) ?></td>
                                        <td>
                                            <?php if ($driver['total_infractions'] > 0): ?>
                                                <span class="badge bg-warning"><?= $driver['total_infractions'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success">0</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Trend data from PHP
        const trendsData = <?= json_encode($data['trends']) ?>;

        // Costs Chart
        const costsChart = new Chart(document.getElementById('costsChart'), {
            type: 'line',
            data: {
                labels: trendsData.costs.map(d => d.period),
                datasets: [
                    {
                        label: 'Carburant',
                        data: trendsData.costs.map(d => d.fuel),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Maintenance',
                        data: trendsData.costs.map(d => d.maintenance),
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Assurance',
                        data: trendsData.costs.map(d => d.insurance),
                        borderColor: '#0dcaf0',
                        backgroundColor: 'rgba(13, 202, 240, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Missions Chart
        const missionsChart = new Chart(document.getElementById('missionsChart'), {
            type: 'bar',
            data: {
                labels: trendsData.missions.map(d => d.period),
                datasets: [
                    {
                        label: 'Total',
                        data: trendsData.missions.map(d => d.total),
                        backgroundColor: 'rgba(13, 110, 253, 0.8)'
                    },
                    {
                        label: 'Terminées',
                        data: trendsData.missions.map(d => d.completed),
                        backgroundColor: 'rgba(25, 135, 84, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Fuel Chart
        const fuelChart = new Chart(document.getElementById('fuelChart'), {
            type: 'line',
            data: {
                labels: trendsData.fuel.map(d => d.period),
                datasets: [{
                    label: 'Quantité (L)',
                    data: trendsData.fuel.map(d => d.total_quantity),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    yAxisID: 'y',
                    tension: 0.4
                }, {
                    label: 'Coût (€)',
                    data: trendsData.fuel.map(d => d.total_cost),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Maintenance Chart
        const maintenanceChart = new Chart(document.getElementById('maintenanceChart'), {
            type: 'doughnut',
            data: {
                labels: ['Préventive', 'Curative'],
                datasets: [{
                    data: [
                        <?= $data['kpis']['maintenance']['preventive'] ?>,
                        <?= $data['kpis']['maintenance']['corrective'] ?>
                    ],
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Export functions
        function exportToPDF() {
            window.location.href = '/analytics/export-pdf?period=<?= $data['period'] ?>';
        }

        function exportToExcel() {
            window.location.href = '/analytics/export-excel?period=<?= $data['period'] ?>';
        }
    </script>
</body>
</html>
