<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-chart-bar"></i> Analyse de Consommation</h1>
            <p class="text-muted">Statistiques et rapports de consommation carburant</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Fleet Overview -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tachometer-alt"></i> Consommation par Véhicule (30 derniers jours)
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($data['fleet_stats'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="fleetTable">
                        <thead>
                            <tr>
                                <th>Véhicule</th>
                                <th>Ravitaillements</th>
                                <th>Total Carburant</th>
                                <th>Coût Total</th>
                                <th>Prix Moyen/L</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['fleet_stats'] as $stat): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($stat['registration_number']); ?></strong><br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($stat['brand'] . ' ' . $stat['model']); ?>
                                        </small>
                                    </td>
                                    <td><?php echo $stat['total_fillups']; ?></td>
                                    <td><?php echo number_format($stat['total_fuel'], 2); ?> L</td>
                                    <td><strong><?php echo number_format($stat['total_cost'], 2); ?> TND</strong></td>
                                    <td><?php echo number_format($stat['avg_price'], 3); ?> TND</td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/fuel/vehicleReport/<?php echo $stat['id']; ?>"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-chart-line"></i> Rapport
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune donnée disponible pour cette période.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-chart-line"></i> Tendances Mensuelles
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($data['monthly_trends'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th>Transactions</th>
                                <th>Total Litres</th>
                                <th>Coût Total</th>
                                <th>Prix Moyen/L</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['monthly_trends'] as $trend): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $date = DateTime::createFromFormat('Y-m', $trend['month']);
                                        echo $date->format('F Y');
                                        ?>
                                    </td>
                                    <td><?php echo $trend['transactions']; ?></td>
                                    <td><?php echo number_format($trend['total_liters'], 2); ?> L</td>
                                    <td><strong><?php echo number_format($trend['total_cost'], 2); ?> TND</strong></td>
                                    <td><?php echo number_format($trend['avg_price'], 3); ?> TND</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <canvas id="monthlyTrendsChart" height="80"></canvas>
            <?php else: ?>
                <p class="text-muted">Aucune donnée disponible.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#fleetTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[3, "desc"]]
    });

    <?php if (!empty($data['monthly_trends'])): ?>
    // Monthly Trends Chart
    const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
    const monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($data['monthly_trends'] as $trend): ?>
                    '<?php echo $trend['month']; ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Coût (TND)',
                data: [
                    <?php foreach ($data['monthly_trends'] as $trend): ?>
                        <?php echo $trend['total_cost']; ?>,
                    <?php endforeach; ?>
                ],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: 'Litres',
                data: [
                    <?php foreach ($data['monthly_trends'] as $trend): ?>
                        <?php echo $trend['total_liters']; ?>,
                    <?php endforeach; ?>
                ],
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Coût (TND)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Litres'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
