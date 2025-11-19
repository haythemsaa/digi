<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-gas-pump"></i> Gestion du Carburant</h1>
            <p class="text-muted">Suivi consommation et économies</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/fuel/addTransaction" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Transaction
            </a>
            <a href="<?php echo APP_URL; ?>/fuel/cards" class="btn btn-success">
                <i class="fas fa-credit-card"></i> Cartes Carburant
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Coût Total (30j)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['stats']['total_cost'] ?? 0, 2); ?> TND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-primary"></i>
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
                                Total Litres
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['stats']['total_liters'] ?? 0, 1); ?> L
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gas-pump fa-2x text-success"></i>
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
                                Prix Moyen/Litre
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['stats']['avg_price_per_liter'] ?? 0, 3); ?> TND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
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
                                Transactions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['total_transactions'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Transactions Récentes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['recent_transactions'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Véhicule</th>
                                        <th>Type</th>
                                        <th>Quantité</th>
                                        <th>Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['recent_transactions'] as $trans): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($trans['registration_number']); ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo strtoupper($trans['fuel_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($trans['quantity_liters'], 2); ?> L</td>
                                            <td><?php echo number_format($trans['total_amount'], 2); ?> TND</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="<?php echo APP_URL; ?>/fuel/transactions" class="btn btn-primary btn-sm">
                            Voir Toutes les Transactions
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Aucune transaction enregistrée.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0"><i class="fas fa-exclamation-triangle"></i> Alertes</h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($data['alerts'])): ?>
                        <?php foreach ($data['alerts'] as $alert): ?>
                            <div class="alert alert-<?php echo $alert['severity'] === 'critical' ? 'danger' : 'warning'; ?> alert-dismissible fade show">
                                <strong><?php echo htmlspecialchars($alert['title']); ?></strong><br>
                                <small><?php echo htmlspecialchars($alert['description']); ?></small>
                                <form method="POST" action="<?php echo APP_URL; ?>/fuel/resolveAlert/<?php echo $alert['id']; ?>" class="mt-2">
                                    <button type="submit" class="btn btn-sm btn-success">Résoudre</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-success"><i class="fas fa-check-circle"></i> Aucune alerte active</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
