<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-users"></i> Passagers</h1>
            <p class="text-muted">Gestion de la base de données clients/passagers</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/addPassenger" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Nouveau Passager
            </a>
        </div>
    </div>

    <!-- Passengers Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Liste des Passagers (<?php echo count($data['passengers']); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['passengers'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="passengersTable">
                                <thead>
                                    <tr>
                                        <th>Code Client</th>
                                        <th>Nom Complet</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Ville</th>
                                        <th>Total Courses</th>
                                        <th>Points Fidélité</th>
                                        <th>Note</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['passengers'] as $passenger): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($passenger['customer_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']); ?></td>
                                            <td>
                                                <i class="fas fa-phone text-success"></i>
                                                <?php echo htmlspecialchars($passenger['phone']); ?>
                                            </td>
                                            <td>
                                                <?php if ($passenger['email']): ?>
                                                    <i class="fas fa-envelope text-info"></i>
                                                    <?php echo htmlspecialchars($passenger['email']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($passenger['city'] ?? '-'); ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    <?php echo $passenger['total_trips']; ?> courses
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-warning">
                                                    <?php echo $passenger['loyalty_points']; ?> pts
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($passenger['rating']): ?>
                                                    <?php
                                                    $rating = floatval($passenger['rating']);
                                                    $stars = '';
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            $stars .= '<i class="fas fa-star text-warning"></i>';
                                                        } elseif ($i - 0.5 <= $rating) {
                                                            $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
                                                        } else {
                                                            $stars .= '<i class="far fa-star text-warning"></i>';
                                                        }
                                                    }
                                                    echo $stars;
                                                    ?>
                                                    <br><small><?php echo number_format($rating, 1); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">Pas de note</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($passenger['is_active']): ?>
                                                    <span class="badge badge-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/passenger_transport/viewPassenger/<?php echo $passenger['id']; ?>"
                                                   class="btn btn-sm btn-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/passenger_transport/editPassenger/<?php echo $passenger['id']; ?>"
                                                   class="btn btn-sm btn-warning" title="Éditer">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Aucun passager enregistré pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#passengersTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 25
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
