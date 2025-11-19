<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-star"></i> Mon Abonnement</h1>
            <p class="text-muted">Gérez vos abonnements et modules actifs</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/subscription-manager" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un Module
            </a>
        </div>
    </div>

    <!-- Subscription Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Abonnements Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $data['stats']['active_subscriptions'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-primary"></i>
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
                                Modules Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo count($data['active_modules']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-puzzle-piece fa-2x text-success"></i>
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
                                Coût Mensuel
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($data['stats']['monthly_revenue'] ?? 0, 2); ?>€
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-info"></i>
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
                                Factures Impayées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $unpaidInvoices = array_filter($data['invoices'], function($inv) {
                                    return $inv['status'] === 'sent' || $inv['status'] === 'overdue';
                                });
                                echo count($unpaidInvoices);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Modules -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-check-circle"></i> Modules Activés</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['active_modules'])): ?>
                        <div class="row">
                            <?php foreach ($data['active_modules'] as $module): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card border-left-success h-100">
                                        <div class="card-body">
                                            <div class="text-center">
                                                <i class="fas <?php echo $module['icon']; ?> fa-3x mb-2"
                                                   style="color: <?php echo $module['color']; ?>"></i>
                                                <h6><?php echo htmlspecialchars($module['module_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($module['description']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Vous n'avez aucun module actif.
                            <a href="<?php echo APP_URL; ?>/subscription-manager" class="alert-link">
                                Souscrire à un module
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Subscriptions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Mes Abonnements
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['subscriptions'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Nom</th>
                                        <th>Cycle</th>
                                        <th>Prix</th>
                                        <th>Début</th>
                                        <th>Prochaine Facturation</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['subscriptions'] as $subscription): ?>
                                        <tr>
                                            <td>
                                                <?php if ($subscription['subscription_type'] === 'module'): ?>
                                                    <span class="badge badge-primary">Module</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Pack</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong>
                                                    <?php echo $subscription['subscription_type'] === 'module'
                                                        ? htmlspecialchars($subscription['module_name'])
                                                        : htmlspecialchars($subscription['pack_name']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php echo $subscription['billing_cycle'] === 'monthly' ? 'Mensuel' : 'Annuel'; ?>
                                            </td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($subscription['price'], 2); ?>€</strong>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($subscription['start_date'])); ?></td>
                                            <td>
                                                <?php if ($subscription['status'] === 'active'): ?>
                                                    <?php echo date('d/m/Y', strtotime($subscription['next_billing_date'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClasses = [
                                                    'active' => 'success',
                                                    'suspended' => 'warning',
                                                    'cancelled' => 'danger',
                                                    'expired' => 'secondary'
                                                ];
                                                $statusTexts = [
                                                    'active' => 'Actif',
                                                    'suspended' => 'Suspendu',
                                                    'cancelled' => 'Annulé',
                                                    'expired' => 'Expiré'
                                                ];
                                                $badgeClass = $statusClasses[$subscription['status']] ?? 'secondary';
                                                $statusText = $statusTexts[$subscription['status']] ?? $subscription['status'];
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($subscription['status'] === 'active'): ?>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="cancelSubscription(<?php echo $subscription['id']; ?>, '<?php echo $subscription['subscription_type'] === 'module' ? htmlspecialchars($subscription['module_name']) : htmlspecialchars($subscription['pack_name']); ?>')">
                                                        <i class="fas fa-times"></i> Annuler
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Vous n'avez pas encore d'abonnement.
                            <a href="<?php echo APP_URL; ?>/subscription-manager" class="alert-link">
                                Découvrir nos formules
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-invoice"></i> Mes Factures
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['invoices'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Date</th>
                                        <th>Échéance</th>
                                        <th>Montant HT</th>
                                        <th>TVA</th>
                                        <th>Total TTC</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['invoices'] as $invoice): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                                            <td><?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($invoice['due_date'])); ?></td>
                                            <td class="text-right"><?php echo number_format($invoice['amount'], 2); ?>€</td>
                                            <td class="text-right"><?php echo number_format($invoice['tax_amount'], 2); ?>€</td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($invoice['total_amount'], 2); ?>€</strong>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClasses = [
                                                    'draft' => 'secondary',
                                                    'sent' => 'info',
                                                    'paid' => 'success',
                                                    'overdue' => 'danger',
                                                    'cancelled' => 'dark'
                                                ];
                                                $statusTexts = [
                                                    'draft' => 'Brouillon',
                                                    'sent' => 'Envoyée',
                                                    'paid' => 'Payée',
                                                    'overdue' => 'En retard',
                                                    'cancelled' => 'Annulée'
                                                ];
                                                $badgeClass = $statusClasses[$invoice['status']] ?? 'secondary';
                                                $statusText = $statusTexts[$invoice['status']] ?? $invoice['status'];
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/subscription-manager/viewInvoice/<?php echo $invoice['id']; ?>"
                                                   class="btn btn-sm btn-info" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> Voir
                                                </a>
                                                <?php if ($invoice['status'] === 'sent' || $invoice['status'] === 'overdue'): ?>
                                                    <button type="button" class="btn btn-sm btn-success"
                                                            onclick="alert('Paiement en ligne - À implémenter')">
                                                        <i class="fas fa-credit-card"></i> Payer
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Aucune facture pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Subscription Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Annuler l'Abonnement</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir annuler votre abonnement à :</p>
                <h5 id="cancel-subscription-name"></h5>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i> L'annulation prendra effet à la fin de votre période de facturation actuelle.
                    Vous conserverez l'accès jusqu'à cette date.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Non, Conserver</button>
                <a href="#" id="confirm-cancel-link" class="btn btn-danger">
                    <i class="fas fa-check"></i> Oui, Annuler
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function cancelSubscription(subscriptionId, subscriptionName) {
    $('#cancel-subscription-name').text(subscriptionName);
    $('#confirm-cancel-link').attr('href', '<?php echo APP_URL; ?>/subscription-manager/cancelMySubscription/' + subscriptionId);
    $('#cancelModal').modal('show');
}
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
