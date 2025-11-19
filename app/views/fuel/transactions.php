<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-list"></i> Transactions Carburant</h1>
            <p class="text-muted">Historique complet des transactions</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel/addTransaction" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Transaction
            </a>
            <a href="<?php echo APP_URL; ?>/fuel" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-gas-pump"></i> Toutes les Transactions
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($data['transactions'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Date</th>
                                <th>Véhicule</th>
                                <th>Type</th>
                                <th>Quantité</th>
                                <th>Prix Unitaire</th>
                                <th>Montant</th>
                                <th>Carte</th>
                                <th>Station</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['transactions'] as $trans): ?>
                                <tr>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars($trans['transaction_number']); ?></small>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($trans['registration_number']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($trans['brand'] . ' ' . $trans['model']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo strtoupper($trans['fuel_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($trans['quantity_liters'], 2); ?> L</td>
                                    <td><?php echo number_format($trans['unit_price'], 3); ?> TND</td>
                                    <td><strong><?php echo number_format($trans['total_amount'], 2); ?> TND</strong></td>
                                    <td>
                                        <?php if ($trans['card_number']): ?>
                                            <span class="badge badge-success">
                                                <?php echo htmlspecialchars($trans['card_number']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($trans['station_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/fuel/viewTransaction/<?php echo $trans['id']; ?>"
                                           class="btn btn-sm btn-info" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune transaction enregistrée.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[1, "desc"]],
        "pageLength": 25
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
