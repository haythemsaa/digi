<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0"><i class="fas fa-chart-line"></i> Analytics Transport de Voyageurs</h1>
            <p class="text-muted">Tableau de bord des performances et statistiques</p>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow border-left-primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Courses Aujourd'hui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['today_bookings']; ?>
                            </div>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> +12% vs hier
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow border-left-success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Revenu Aujourd'hui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['stats']['today_revenue'], 2); ?> TND
                            </div>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> +18% vs hier
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow border-left-info h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Courses Mois (Complétées)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['month_completed_trips']; ?>
                            </div>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: 75%"></div>
                            </div>
                            <small class="text-muted">75% de l'objectif</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow border-left-warning h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chauffeurs Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['available_drivers']; ?>
                                <small class="text-muted">/ <?php echo $data['stats']['total_passengers']; ?></small>
                            </div>
                            <small class="text-muted">
                                Taux d'occupation: 68%
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Revenue Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Évolution du Revenu (30 derniers jours)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Booking Type Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-pie"></i> Répartition par Type
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="bookingTypeChart"></canvas>
                    <div class="mt-3 text-center">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="text-warning mb-0">65%</h5>
                                <small class="text-muted">Taxi</small>
                            </div>
                            <div class="col-6">
                                <h5 class="text-primary mb-0">35%</h5>
                                <small class="text-muted">Bus</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <!-- Peak Hours -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-clock"></i> Heures de Pointe
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="peakHoursChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Routes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-route"></i> Top 5 Trajets Populaires
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Centre-Ville → Aéroport</strong>
                                <br><small class="text-muted">Tarif moyen: 28 TND</small>
                            </div>
                            <span class="badge badge-primary badge-pill">156 courses</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Gare → La Marsa</strong>
                                <br><small class="text-muted">Tarif moyen: 15 TND</small>
                            </div>
                            <span class="badge badge-primary badge-pill">124 courses</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Carthage → Centre-Ville</strong>
                                <br><small class="text-muted">Tarif moyen: 12 TND</small>
                            </div>
                            <span class="badge badge-primary badge-pill">98 courses</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Lac 2 → Aéroport</strong>
                                <br><small class="text-muted">Tarif moyen: 18 TND</small>
                            </div>
                            <span class="badge badge-primary badge-pill">87 courses</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Bardo → Tunis Marine</strong>
                                <br><small class="text-muted">Tarif moyen: 8 TND</small>
                            </div>
                            <span class="badge badge-primary badge-pill">76 courses</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Driver Performance -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy"></i> Top 10 Chauffeurs du Mois
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Chauffeur</th>
                                    <th>Courses</th>
                                    <th>Distance (km)</th>
                                    <th>Revenu Brut</th>
                                    <th>Commission</th>
                                    <th>Note Moyenne</th>
                                    <th>Temps en Ligne</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $topDrivers = [
                                    ['name' => 'Ahmed Ben Ali', 'trips' => 187, 'distance' => 2456, 'revenue' => 4280, 'commission' => 856, 'rating' => 4.9, 'hours' => 168],
                                    ['name' => 'Mohamed Saidi', 'trips' => 165, 'distance' => 2234, 'revenue' => 3850, 'commission' => 770, 'rating' => 4.8, 'hours' => 152],
                                    ['name' => 'Karim Jebali', 'trips' => 158, 'distance' => 2145, 'revenue' => 3620, 'commission' => 724, 'rating' => 4.7, 'hours' => 145],
                                    ['name' => 'Ali Mansour', 'trips' => 142, 'distance' => 1987, 'revenue' => 3340, 'commission' => 668, 'rating' => 4.8, 'hours' => 138],
                                    ['name' => 'Sami Bouaziz', 'trips' => 138, 'distance' => 1865, 'revenue' => 3180, 'commission' => 636, 'rating' => 4.6, 'hours' => 142],
                                    ['name' => 'Youssef Mejri', 'trips' => 125, 'distance' => 1654, 'revenue' => 2890, 'commission' => 578, 'rating' => 4.7, 'hours' => 128],
                                    ['name' => 'Mehdi Trabelsi', 'trips' => 118, 'distance' => 1542, 'revenue' => 2650, 'commission' => 530, 'rating' => 4.5, 'hours' => 124],
                                    ['name' => 'Nabil Hamdi', 'trips' => 112, 'distance' => 1456, 'revenue' => 2520, 'commission' => 504, 'rating' => 4.6, 'hours' => 118],
                                    ['name' => 'Fathi Gharbi', 'trips' => 105, 'distance' => 1365, 'revenue' => 2380, 'commission' => 476, 'rating' => 4.4, 'hours' => 115],
                                    ['name' => 'Rami Chouchane', 'trips' => 98, 'distance' => 1287, 'revenue' => 2240, 'commission' => 448, 'rating' => 4.5, 'hours' => 108]
                                ];

                                foreach ($topDrivers as $index => $driver):
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if ($index < 3): ?>
                                                <span class="badge badge-<?php echo $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'bronze'); ?> p-2">
                                                    <?php echo $index + 1; ?>
                                                </span>
                                            <?php else: ?>
                                                <?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo $driver['name']; ?></strong></td>
                                        <td><?php echo $driver['trips']; ?></td>
                                        <td><?php echo number_format($driver['distance']); ?> km</td>
                                        <td><?php echo number_format($driver['revenue'], 2); ?> TND</td>
                                        <td><?php echo number_format($driver['commission'], 2); ?> TND</td>
                                        <td>
                                            <span class="badge badge-success">
                                                <i class="fas fa-star"></i> <?php echo $driver['rating']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $driver['hours']; ?>h</td>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['J-30', 'J-25', 'J-20', 'J-15', 'J-10', 'J-5', 'Aujourd\'hui'],
        datasets: [{
            label: 'Revenu (TND)',
            data: [3200, 3800, 4200, 3900, 4500, 4800, 5200],
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
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Booking Type Chart
const bookingTypeCtx = document.getElementById('bookingTypeChart').getContext('2d');
new Chart(bookingTypeCtx, {
    type: 'doughnut',
    data: {
        labels: ['Taxi', 'Bus', 'Navette'],
        datasets: [{
            data: [65, 30, 5],
            backgroundColor: [
                'rgb(255, 193, 7)',
                'rgb(78, 115, 223)',
                'rgb(28, 200, 138)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Peak Hours Chart
const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');
new Chart(peakHoursCtx, {
    type: 'bar',
    data: {
        labels: ['6h', '8h', '10h', '12h', '14h', '16h', '18h', '20h', '22h'],
        datasets: [{
            label: 'Nombre de courses',
            data: [12, 45, 32, 28, 25, 38, 62, 48, 28],
            backgroundColor: 'rgba(54, 185, 204, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<style>
.badge-bronze {
    background-color: #CD7F32;
    color: white;
}
</style>

<?php require_once '../app/views/includes/footer.php'; ?>
