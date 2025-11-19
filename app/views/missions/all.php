<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-list"></i> Toutes les Missions</h1>
            <p class="text-muted">Liste complète des missions</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/missions/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Mission
            </a>
            <a href="<?php echo APP_URL; ?>/missions" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Status Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo APP_URL; ?>/missions/all" class="form-inline">
                <label class="mr-2">Filtrer par statut:</label>
                <select name="status" class="form-control mr-2">
                    <option value="">Tous</option>
                    <option value="pending" <?php echo (isset($data['filter_status']) && $data['filter_status'] === 'pending') ? 'selected' : ''; ?>>
                        En attente
                    </option>
                    <option value="assigned" <?php echo (isset($data['filter_status']) && $data['filter_status'] === 'assigned') ? 'selected' : ''; ?>>
                        Assignées
                    </option>
                    <option value="in_progress" <?php echo (isset($data['filter_status']) && $data['filter_status'] === 'in_progress') ? 'selected' : ''; ?>>
                        En cours
                    </option>
                    <option value="completed" <?php echo (isset($data['filter_status']) && $data['filter_status'] === 'completed') ? 'selected' : ''; ?>>
                        Terminées
                    </option>
                    <option value="cancelled" <?php echo (isset($data['filter_status']) && $data['filter_status'] === 'cancelled') ? 'selected' : ''; ?>>
                        Annulées
                    </option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tasks"></i> Missions
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($data['missions'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="missionsTable">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Type</th>
                                <th>Client</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Véhicule</th>
                                <th>Conducteur</th>
                                <th>Date Planifiée</th>
                                <th>Facturation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['missions'] as $mission): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($mission['mission_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'delivery' => 'Livraison',
                                            'pickup' => 'Ramassage',
                                            'transport' => 'Transport',
                                            'service' => 'Service',
                                            'maintenance' => 'Maintenance',
                                            'other' => 'Autre'
                                        ];
                                        echo $typeLabels[$mission['mission_type']] ?? $mission['mission_type'];
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($mission['client_name']); ?></td>
                                    <td>
                                        <?php
                                        $priorityBadges = [
                                            'low' => 'secondary',
                                            'normal' => 'info',
                                            'high' => 'warning',
                                            'urgent' => 'danger'
                                        ];
                                        $badge = $priorityBadges[$mission['priority']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?php echo $badge; ?>">
                                            <?php echo strtoupper($mission['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'pending' => 'warning',
                                            'assigned' => 'info',
                                            'in_progress' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            'on_hold' => 'secondary'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'En attente',
                                            'assigned' => 'Assignée',
                                            'in_progress' => 'En cours',
                                            'completed' => 'Terminée',
                                            'cancelled' => 'Annulée',
                                            'on_hold' => 'En pause'
                                        ];
                                        $badge = $statusBadges[$mission['status']] ?? 'secondary';
                                        $label = $statusLabels[$mission['status']] ?? $mission['status'];
                                        ?>
                                        <span class="badge badge-<?php echo $badge; ?>">
                                            <?php echo $label; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($mission['registration_number'] ?? 'Non assigné'); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($mission['driver_name'] ?? 'Non assigné'); ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($mission['scheduled_start']) {
                                            echo date('d/m/Y H:i', strtotime($mission['scheduled_start']));
                                        } else {
                                            echo '<span class="text-muted">N/A</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($mission['total_amount']): ?>
                                            <?php echo number_format($mission['total_amount'], 2); ?> TND
                                            <br>
                                            <?php
                                            $paymentBadge = $mission['payment_status'] === 'paid' ? 'success' : 'warning';
                                            ?>
                                            <span class="badge badge-<?php echo $paymentBadge; ?>">
                                                <?php echo ucfirst($mission['payment_status']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Pas de facture</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/missions/view/<?php echo $mission['id']; ?>"
                                           class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/missions/edit/<?php echo $mission['id']; ?>"
                                           class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune mission trouvée.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#missionsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 25
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
