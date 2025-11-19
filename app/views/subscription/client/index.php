<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4 text-center">
        <div class="col-md-12">
            <h1 class="display-4"><i class="fas fa-star"></i> Choisissez Votre Plan</h1>
            <p class="lead text-muted">Solutions modulaires pour optimiser la gestion de votre flotte</p>
        </div>
    </div>

    <!-- Pricing Toggle -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary active" id="monthly-toggle">
                    <input type="radio" name="billing" value="monthly" checked> Mensuel
                </label>
                <label class="btn btn-outline-primary" id="yearly-toggle">
                    <input type="radio" name="billing" value="yearly"> Annuel
                    <span class="badge badge-success ml-2">-17%</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Featured Packs -->
    <h3 class="text-center mb-4"><i class="fas fa-boxes"></i> Formules Complètes</h3>
    <div class="row mb-5">
        <?php foreach ($data['packs'] as $pack): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100 <?php echo $pack['is_featured'] ? 'border-primary' : ''; ?>">
                    <?php if ($pack['is_featured']): ?>
                        <div class="card-header bg-primary text-white text-center">
                            <i class="fas fa-star"></i> <strong>RECOMMANDÉ</strong>
                        </div>
                    <?php endif; ?>

                    <div class="card-body text-center">
                        <h4 class="card-title"><?php echo htmlspecialchars($pack['pack_name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($pack['description']); ?></p>

                        <div class="my-4">
                            <h2 class="text-primary mb-0">
                                <span class="pack-price" data-monthly="<?php echo $pack['price_monthly']; ?>" data-yearly="<?php echo $pack['price_yearly']; ?>">
                                    <?php echo number_format($pack['price_monthly'], 2); ?>
                                </span>€
                            </h2>
                            <small class="text-muted billing-period">par mois</small>

                            <?php if ($pack['discount_percent'] > 0): ?>
                                <div class="mt-2">
                                    <span class="badge badge-success">
                                        <i class="fas fa-tag"></i> Économie: <?php echo number_format($pack['discount_percent'], 0); ?>%
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <ul class="list-unstyled text-left">
                            <?php foreach ($pack['modules'] as $module): ?>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-2"></i>
                                    <strong><?php echo htmlspecialchars($module['module_name']); ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <button type="button" class="btn btn-primary btn-block mt-3"
                                onclick="subscribeToPack(<?php echo $pack['id']; ?>, '<?php echo htmlspecialchars($pack['pack_name']); ?>')">
                            <i class="fas fa-shopping-cart"></i> Souscrire
                        </button>

                        <button type="button" class="btn btn-outline-secondary btn-sm btn-block mt-2"
                                onclick="viewPackDetails(<?php echo $pack['id']; ?>)">
                            <i class="fas fa-info-circle"></i> Voir les détails
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Individual Modules -->
    <h3 class="text-center mb-4"><i class="fas fa-puzzle-piece"></i> Modules Individuels</h3>
    <p class="text-center text-muted mb-4">Composez votre solution sur mesure</p>

    <div class="row">
        <?php foreach ($data['modules'] as $module): ?>
            <div class="col-md-3 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <i class="fas <?php echo $module['icon']; ?> fa-3x mb-3"
                           style="color: <?php echo $module['color']; ?>"></i>

                        <h5 class="card-title"><?php echo htmlspecialchars($module['module_name']); ?></h5>
                        <p class="text-muted small"><?php echo htmlspecialchars($module['description']); ?></p>

                        <h3 class="text-primary mt-3 mb-0">
                            <span class="module-price" data-monthly="<?php echo $module['price_monthly']; ?>" data-yearly="<?php echo $module['price_yearly']; ?>">
                                <?php echo number_format($module['price_monthly'], 2); ?>
                            </span>€
                        </h3>
                        <small class="text-muted billing-period">par mois</small>

                        <button type="button" class="btn btn-outline-primary btn-block mt-3"
                                onclick="subscribeToModule(<?php echo $module['id']; ?>, '<?php echo htmlspecialchars($module['module_name']); ?>')">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>

                        <?php if ($module['features']): ?>
                            <button type="button" class="btn btn-link btn-sm btn-block"
                                    onclick="viewModuleFeatures(<?php echo $module['id']; ?>, '<?php echo htmlspecialchars($module['module_name']); ?>', <?php echo htmlspecialchars($module['features']); ?>)">
                                <i class="fas fa-list-ul"></i> Fonctionnalités
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Features Comparison Section -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-table"></i> Comparaison des Formules</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Module</th>
                                    <?php foreach ($data['packs'] as $pack): ?>
                                        <th class="text-center"><?php echo htmlspecialchars($pack['pack_name']); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['modules'] as $module): ?>
                                    <tr>
                                        <td>
                                            <i class="fas <?php echo $module['icon']; ?> mr-2" style="color: <?php echo $module['color']; ?>"></i>
                                            <strong><?php echo htmlspecialchars($module['module_name']); ?></strong>
                                        </td>
                                        <?php foreach ($data['packs'] as $pack): ?>
                                            <td class="text-center">
                                                <?php
                                                $included = false;
                                                foreach ($pack['modules'] as $packModule) {
                                                    if ($packModule['id'] == $module['id']) {
                                                        $included = true;
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <?php if ($included): ?>
                                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle fa-2x text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-primary">
                                    <th>Prix Mensuel</th>
                                    <?php foreach ($data['packs'] as $pack): ?>
                                        <th class="text-center">
                                            <h5 class="mb-0"><?php echo number_format($pack['price_monthly'], 2); ?>€</h5>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subscription Confirmation Modal -->
<div class="modal fade" id="subscribeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?php echo APP_URL; ?>/subscription-manager/subscribe">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-shopping-cart"></i> Confirmer l'Abonnement</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5 id="subscribe-item-name"></h5>
                    <p class="text-muted" id="subscribe-item-description"></p>

                    <input type="hidden" name="subscription_type" id="subscription_type">
                    <input type="hidden" name="module_id" id="module_id">
                    <input type="hidden" name="pack_id" id="pack_id">

                    <div class="form-group">
                        <label>Cycle de Facturation</label>
                        <select class="form-control" name="billing_cycle" required>
                            <option value="monthly">Mensuel</option>
                            <option value="yearly">Annuel (2 mois gratuits)</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Une facture sera générée après confirmation.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Confirmer l'Abonnement
                    </button>
                </div>
            </form>
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
            <div class="modal-body" id="featuresContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle billing period
$('#monthly-toggle, #yearly-toggle').on('click', function() {
    var billing = $(this).find('input').val();
    updatePrices(billing);
});

function updatePrices(billing) {
    if (billing === 'yearly') {
        $('.billing-period').text('par an');
        $('.pack-price, .module-price').each(function() {
            var yearly = $(this).data('yearly');
            $(this).text(parseFloat(yearly).toFixed(2));
        });
    } else {
        $('.billing-period').text('par mois');
        $('.pack-price, .module-price').each(function() {
            var monthly = $(this).data('monthly');
            $(this).text(parseFloat(monthly).toFixed(2));
        });
    }
}

function subscribeToModule(moduleId, moduleName) {
    $('#subscription_type').val('module');
    $('#module_id').val(moduleId);
    $('#pack_id').val('');
    $('#subscribe-item-name').text(moduleName);
    $('#subscribe-item-description').text('Module individuel');
    $('#subscribeModal').modal('show');
}

function subscribeToPack(packId, packName) {
    $('#subscription_type').val('pack');
    $('#pack_id').val(packId);
    $('#module_id').val('');
    $('#subscribe-item-name').text(packName);
    $('#subscribe-item-description').text('Formule complète');
    $('#subscribeModal').modal('show');
}

function viewModuleFeatures(moduleId, moduleName, features) {
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

function viewPackDetails(packId) {
    // Could redirect to detailed pack page or show in modal
    alert('Détails du pack #' + packId);
}
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
