<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?? 'Suivi Carbone & RSE' ?> - Pakiparc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .carbon-card {
            border-left: 4px solid #198754;
            transition: transform 0.2s;
        }
        .carbon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .eco-badge {
            display: inline-block;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            text-align: center;
            line-height: 60px;
            font-weight: bold;
            color: white;
        }
        .eco-badge.excellent { background: #198754; }
        .eco-badge.good { background: #20c997; }
        .eco-badge.average { background: #ffc107; }
        .eco-badge.poor { background: #fd7e14; }
        .eco-badge.bad { background: #dc3545; }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1><i class="fas fa-leaf text-success me-2"></i><?= $data['title'] ?></h1>
                <p class="text-muted">Suivez votre empreinte carbone et engagements RSE</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group me-2">
                    <a href="?period=month" class="btn btn-outline-success <?= $data['period'] === 'month' ? 'active' : '' ?>">Mois</a>
                    <a href="?period=quarter" class="btn btn-outline-success <?= $data['period'] === 'quarter' ? 'active' : '' ?>">Trimestre</a>
                    <a href="?period=year" class="btn btn-outline-success <?= $data['period'] === 'year' ? 'active' : '' ?>">Année</a>
                </div>
                <a href="/carbon/csr-report" class="btn btn-success">
                    <i class="fas fa-file-pdf me-1"></i>Rapport RSE
                </a>
            </div>
        </div>

        <!-- Carbon Footprint KPIs -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card carbon-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-smog fa-3x text-success mb-3"></i>
                        <h6 class="text-muted">Émissions CO₂</h6>
                        <h2 class="text-success"><?= number_format($data['footprint']['total_co2_tons'], 2) ?></h2>
                        <p class="mb-0">tonnes</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card carbon-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-road fa-3x text-info mb-3"></i>
                        <h6 class="text-muted">Distance Totale</h6>
                        <h2 class="text-info"><?= number_format($data['footprint']['total_distance'], 0) ?></h2>
                        <p class="mb-0">kilomètres</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card carbon-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-gas-pump fa-3x text-warning mb-3"></i>
                        <h6 class="text-muted">Carburant</h6>
                        <h2 class="text-warning"><?= number_format($data['footprint']['total_fuel'], 0) ?></h2>
                        <p class="mb-0">litres</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card carbon-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-euro-sign fa-3x text-danger mb-3"></i>
                        <h6 class="text-muted">Coût Compensation</h6>
                        <h2 class="text-danger"><?= number_format($data['footprint']['compensation_cost'], 2) ?> €</h2>
                        <p class="mb-0">pour neutralité</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-8 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Évolution Émissions CO₂</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="co2TrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie me-2"></i>Répartition par Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="fuelTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Eco-Driving Recommendations -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Recommandations Éco-Conduite</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($data['recommendations'] as $rec): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="text-success">
                                            <i class="fas fa-check-circle me-1"></i><?= $rec['tip'] ?>
                                            <span class="badge bg-success float-end">-<?= $rec['reduction_percent'] ?>%</span>
                                        </h6>
                                        <p class="text-muted small"><?= $rec['description'] ?></p>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Réduction CO₂:</small><br>
                                                <strong><?= number_format($rec['co2_reduction'], 0) ?> kg</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Économie:</small><br>
                                                <strong class="text-success"><?= number_format($rec['cost_saving'], 2) ?> €</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
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
                        <h5><i class="fas fa-car me-2"></i>Performance Véhicules</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Véhicule</th>
                                        <th>Distance</th>
                                        <th>CO₂/km</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['vehicle_performance'] as $vehicle): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($vehicle['registration_number']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?></small>
                                        </td>
                                        <td><?= number_format($vehicle['total_distance'], 0) ?> km</td>
                                        <td><?= number_format($vehicle['co2_per_km'], 3) ?> kg</td>
                                        <td>
                                            <span class="eco-badge <?php
                                                if ($vehicle['eco_score'] >= 80) echo 'excellent';
                                                elseif ($vehicle['eco_score'] >= 60) echo 'good';
                                                elseif ($vehicle['eco_score'] >= 40) echo 'average';
                                                elseif ($vehicle['eco_score'] >= 20) echo 'poor';
                                                else echo 'bad';
                                            ?>"><?= $vehicle['eco_score'] ?></span>
                                        </td>
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
                        <h5><i class="fas fa-user-tie me-2"></i>Performance Conducteurs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Conducteur</th>
                                        <th>Distance</th>
                                        <th>CO₂ Total</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['driver_performance'] as $driver): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></strong>
                                        </td>
                                        <td><?= number_format($driver['total_distance'], 0) ?> km</td>
                                        <td><?= number_format($driver['total_co2'], 0) ?> kg</td>
                                        <td>
                                            <span class="eco-badge <?php
                                                if ($driver['eco_score'] >= 80) echo 'excellent';
                                                elseif ($driver['eco_score'] >= 60) echo 'good';
                                                elseif ($driver['eco_score'] >= 40) echo 'average';
                                                elseif ($driver['eco_score'] >= 20) echo 'poor';
                                                else echo 'bad';
                                            ?>"><?= $driver['eco_score'] ?></span>
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

        <!-- Info Box -->
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle me-2"></i>À propos du Suivi Carbone</h5>
            <p class="mb-2">
                <strong>Facteurs d'émission:</strong> Basés sur les données de l'ADEME (Agence de l'Environnement et de la Maîtrise de l'Énergie - France).
            </p>
            <p class="mb-2">
                <strong>Objectifs UE 2030:</strong> Réduction de 55% des émissions vs 1990
            </p>
            <p class="mb-0">
                <strong>Directive CSRD:</strong> Rapports de durabilité obligatoires pour certaines entreprises.
                <a href="https://ec.europa.eu/info/business-economy-euro/company-reporting-and-auditing/company-reporting/corporate-sustainability-reporting_en" target="_blank" class="alert-link">En savoir plus</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const trendData = <?= json_encode($data['trend']) ?>;

        // CO2 Trend Chart
        new Chart(document.getElementById('co2TrendChart'), {
            type: 'line',
            data: {
                labels: trendData.map(d => d.month),
                datasets: [{
                    label: 'Émissions CO₂ (kg)',
                    data: trendData.map(d => d.co2),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Fuel Type Chart
        const breakdown = <?= json_encode($data['footprint']['breakdown']) ?>;
        const fuelTypes = Object.keys(breakdown);
        const fuelCO2 = Object.values(breakdown).map(b => b.co2);

        new Chart(document.getElementById('fuelTypeChart'), {
            type: 'doughnut',
            data: {
                labels: fuelTypes.map(t => t.charAt(0).toUpperCase() + t.slice(1)),
                datasets: [{
                    data: fuelCO2,
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(13, 202, 240, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>
