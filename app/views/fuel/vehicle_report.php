<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line"></i> Rapport Consommation -
                <?php echo htmlspecialchars($data['vehicle']['registration_number']); ?>
            </h1>
            <p class="text-muted">
                <?php echo htmlspecialchars($data['vehicle']['brand'] . ' ' . $data['vehicle']['model']); ?>
            </p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel/analytics" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo APP_URL; ?>/fuel/vehicleReport/<?php echo $data['vehicle']['id']; ?>" class="form-inline">
                <label class="mr-2">Période:</label>
                <input type="date" class="form-control mr-2" name="start_date"
                       value="<?php echo $data['start_date']; ?>">
                <label class="mr-2">au</label>
                <input type="date" class="form-control mr-2" name="end_date"
                       value="<?php echo $data['end_date']; ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-3">
            <div class="card shadow border-left-primary mb-4">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Carburant
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo number_format($data['consumption']['total_fuel'] ?? 0, 2); ?> L
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-success mb-4">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Coût Total
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo number_format($data['consumption']['total_cost'] ?? 0, 2); ?> TND
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-info mb-4">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Consommation
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php
                        if (isset($data['consumption']['consumption_per_100km'])) {
                            echo number_format($data['consumption']['consumption_per_100km'], 2) . ' L/100km';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-left-warning mb-4">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Coût/km
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php
                        if (isset($data['consumption']['cost_per_km'])) {
                            echo number_format($data['consumption']['cost_per_km'], 3) . ' TND';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Details -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Détails
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Nombre de Ravitaillements</th>
                            <td><?php echo $data['consumption']['total_fillups'] ?? 0; ?></td>
                        </tr>
                        <tr>
                            <th>Distance Parcourue</th>
                            <td><?php echo number_format($data['consumption']['distance_km'] ?? 0); ?> km</td>
                        </tr>
                        <tr>
                            <th>Prix Moyen/Litre</th>
                            <td><?php echo number_format($data['consumption']['avg_price_per_liter'] ?? 0, 3); ?> TND</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-car"></i> Informations Véhicule
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Immatriculation</th>
                            <td><?php echo htmlspecialchars($data['vehicle']['registration_number']); ?></td>
                        </tr>
                        <tr>
                            <th>Marque/Modèle</th>
                            <td><?php echo htmlspecialchars($data['vehicle']['brand'] . ' ' . $data['vehicle']['model']); ?></td>
                        </tr>
                        <tr>
                            <th>Type de Carburant</th>
                            <td><?php echo strtoupper($data['vehicle']['fuel_type'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Année</th>
                            <td><?php echo $data['vehicle']['year'] ?? 'N/A'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($data['consumption']['consumption_per_100km'])): ?>
        <!-- Performance Analysis -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-tachometer-alt"></i> Analyse de Performance
                </h6>
            </div>
            <div class="card-body">
                <?php
                $consumption = $data['consumption']['consumption_per_100km'];
                $threshold_good = 7.0;
                $threshold_average = 10.0;

                if ($consumption < $threshold_good) {
                    $badge = 'success';
                    $message = 'Excellente consommation! Le véhicule est très économique.';
                } elseif ($consumption < $threshold_average) {
                    $badge = 'warning';
                    $message = 'Consommation correcte. Des économies supplémentaires sont possibles.';
                } else {
                    $badge = 'danger';
                    $message = 'Consommation élevée. Vérifier l\'entretien et le style de conduite.';
                }
                ?>
                <div class="alert alert-<?php echo $badge; ?>">
                    <i class="fas fa-chart-line"></i>
                    <strong><?php echo $message; ?></strong>
                </div>

                <h6>Recommandations:</h6>
                <ul>
                    <li>Vérifier régulièrement la pression des pneus</li>
                    <li>Effectuer l'entretien selon le calendrier recommandé</li>
                    <li>Former les conducteurs à l'éco-conduite</li>
                    <li>Éviter les accélérations brusques et les arrêts fréquents</li>
                </ul>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> Pour calculer la consommation, assurez-vous d'enregistrer le kilométrage
            lors de chaque ravitaillement en plein.
        </div>
    <?php endif; ?>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
