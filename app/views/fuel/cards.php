<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-credit-card"></i> Cartes Carburant</h1>
            <p class="text-muted">Gestion des cartes carburant</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel/addCard" class="btn btn-success">
                <i class="fas fa-plus"></i> Nouvelle Carte
            </a>
            <a href="<?php echo APP_URL; ?>/fuel" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-credit-card"></i> Liste des Cartes
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($data['cards'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="cardsTable">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Type</th>
                                <th>Fournisseur</th>
                                <th>Véhicule</th>
                                <th>Conducteur</th>
                                <th>Limite Jour</th>
                                <th>Limite Mois</th>
                                <th>Expiration</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['cards'] as $card): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($card['card_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($card['card_type'] === 'physical'): ?>
                                            <span class="badge badge-primary">Physique</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Virtuelle</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($card['provider'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($card['registration_number']): ?>
                                            <?php echo htmlspecialchars($card['registration_number']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non assigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($card['driver_name']): ?>
                                            <?php echo htmlspecialchars($card['driver_name']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non assigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($card['daily_limit']): ?>
                                            <?php echo number_format($card['daily_limit'], 2); ?> TND
                                        <?php else: ?>
                                            <span class="text-muted">Illimité</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($card['monthly_limit']): ?>
                                            <?php echo number_format($card['monthly_limit'], 2); ?> TND
                                        <?php else: ?>
                                            <span class="text-muted">Illimité</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($card['expiry_date']): ?>
                                            <?php
                                            $expiryDate = strtotime($card['expiry_date']);
                                            $isExpired = $expiryDate < time();
                                            $isExpiringSoon = $expiryDate < strtotime('+30 days');
                                            ?>
                                            <span class="<?php echo $isExpired ? 'text-danger' : ($isExpiringSoon ? 'text-warning' : ''); ?>">
                                                <?php echo date('d/m/Y', $expiryDate); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($card['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune carte enregistrée.</p>
                <a href="<?php echo APP_URL; ?>/fuel/addCard" class="btn btn-success">
                    <i class="fas fa-plus"></i> Créer la première carte
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#cardsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "pageLength": 25
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
