<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-calendar"></i> Planning des Missions</h1>
            <p class="text-muted">Vue calendrier des missions planifiées</p>
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

    <!-- Month Navigation -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a href="<?php echo APP_URL; ?>/missions/calendar?month=<?php echo date('m', strtotime('-1 month', strtotime($data['year'] . '-' . $data['month'] . '-01'))); ?>&year=<?php echo date('Y', strtotime('-1 month', strtotime($data['year'] . '-' . $data['month'] . '-01'))); ?>"
                       class="btn btn-secondary">
                        <i class="fas fa-chevron-left"></i> Mois Précédent
                    </a>
                </div>
                <div class="col-md-4 text-center">
                    <h4>
                        <?php
                        $monthNames = [
                            '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
                            '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
                            '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
                        ];
                        echo $monthNames[$data['month']] . ' ' . $data['year'];
                        ?>
                    </h4>
                </div>
                <div class="col-md-4 text-right">
                    <a href="<?php echo APP_URL; ?>/missions/calendar?month=<?php echo date('m', strtotime('+1 month', strtotime($data['year'] . '-' . $data['month'] . '-01'))); ?>&year=<?php echo date('Y', strtotime('+1 month', strtotime($data['year'] . '-' . $data['month'] . '-01'))); ?>"
                       class="btn btn-secondary">
                        Mois Suivant <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Missions List for Selected Month -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Missions du Mois
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($data['missions'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Date/Heure</th>
                                <th>Mission</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Véhicule</th>
                                <th>Conducteur</th>
                                <th>Statut</th>
                                <th>Priorité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Group missions by date
                            $missionsByDate = [];
                            foreach ($data['missions'] as $mission) {
                                $date = date('Y-m-d', strtotime($mission['scheduled_start']));
                                $missionsByDate[$date][] = $mission;
                            }

                            // Display missions grouped by date
                            foreach ($missionsByDate as $date => $missions):
                                ?>
                                <tr class="table-secondary">
                                    <td colspan="9">
                                        <strong>
                                            <i class="fas fa-calendar-day"></i>
                                            <?php echo date('l d F Y', strtotime($date)); ?>
                                        </strong>
                                    </td>
                                </tr>
                                <?php foreach ($missions as $mission): ?>
                                    <tr>
                                        <td><?php echo date('H:i', strtotime($mission['scheduled_start'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($mission['mission_number']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($mission['client_name']); ?></td>
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
                                        <td>
                                            <?php echo htmlspecialchars($mission['registration_number'] ?? 'Non assigné'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($mission['driver_name'] ?? 'Non assigné'); ?>
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
                                            <a href="<?php echo APP_URL; ?>/missions/view/<?php echo $mission['id']; ?>"
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5>Aucune mission planifiée pour ce mois</h5>
                    <p class="text-muted">Créez de nouvelles missions pour les voir apparaître ici.</p>
                    <a href="<?php echo APP_URL; ?>/missions/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer une Mission
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="card shadow mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-secondary">
                <i class="fas fa-info-circle"></i> Légende
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Statuts</h6>
                    <span class="badge badge-warning">En attente</span>
                    <span class="badge badge-info">Assignée</span>
                    <span class="badge badge-primary">En cours</span>
                    <span class="badge badge-success">Terminée</span>
                    <span class="badge badge-secondary">En pause</span>
                    <span class="badge badge-danger">Annulée</span>
                </div>
                <div class="col-md-6">
                    <h6>Priorités</h6>
                    <span class="badge badge-secondary">LOW</span>
                    <span class="badge badge-info">NORMAL</span>
                    <span class="badge badge-warning">HIGH</span>
                    <span class="badge badge-danger">URGENT</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
