<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-puzzle-piece"></i> Modules d'Abonnement</h1>
            <p class="text-muted">Gestion des modules individuels</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/subscription-manager/createModule" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau Module
            </a>
            <a href="<?php echo APP_URL; ?>/subscription-manager/packs" class="btn btn-success">
                <i class="fas fa-boxes"></i> Voir les Packs
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Tous les Modules (<?php echo count($data['modules']); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['modules'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="modulesTable">
                                <thead>
                                    <tr>
                                        <th width="60">Ordre</th>
                                        <th>Code</th>
                                        <th>Nom du Module</th>
                                        <th>Description</th>
                                        <th width="120">Prix/Mois</th>
                                        <th width="120">Prix/An</th>
                                        <th width="80">Statut</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['modules'] as $module): ?>
                                        <tr>
                                            <td class="text-center"><?php echo $module['sort_order']; ?></td>
                                            <td>
                                                <code class="badge badge-secondary"><?php echo htmlspecialchars($module['module_code']); ?></code>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas <?php echo $module['icon']; ?> fa-2x mr-3" style="color: <?php echo $module['color']; ?>"></i>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($module['module_name']); ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($module['description']); ?></small>
                                            </td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($module['price_monthly'], 2); ?> €</strong>
                                            </td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($module['price_yearly'], 2); ?> €</strong>
                                                <?php if ($module['price_yearly'] < $module['price_monthly'] * 12): ?>
                                                    <br><small class="text-success">
                                                        Économie: <?php echo number_format(100 - ($module['price_yearly'] / ($module['price_monthly'] * 12)) * 100, 0); ?>%
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($module['is_active']): ?>
                                                    <span class="badge badge-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/subscription-manager/createModule/<?php echo $module['id']; ?>"
                                                   class="btn btn-sm btn-info" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-secondary" title="Voir les fonctionnalités"
                                                        onclick="viewFeatures(<?php echo $module['id']; ?>, '<?php echo htmlspecialchars($module['module_name']); ?>', <?php echo htmlspecialchars($module['features'] ?? '[]'); ?>)">
                                                    <i class="fas fa-list-ul"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Aucun module enregistré.
                            <a href="<?php echo APP_URL; ?>/subscription-manager/createModule" class="alert-link">
                                Créer le premier module
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Modal -->
<div class="modal fade" id="featuresModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fonctionnalités</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="featuresContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#modulesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "asc"]],
        "pageLength": 25
    });
});

function viewFeatures(moduleId, moduleName, features) {
    var html = '<h6>' + moduleName + '</h6><ul class="list-group">';

    if (features && features.length > 0) {
        features.forEach(function(feature) {
            html += '<li class="list-group-item"><i class="fas fa-check text-success mr-2"></i>' + feature + '</li>';
        });
    } else {
        html += '<li class="list-group-item text-muted">Aucune fonctionnalité définie</li>';
    }

    html += '</ul>';

    $('#featuresContent').html(html);
    $('#featuresModal').modal('show');
}
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
