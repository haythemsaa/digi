<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-boxes"></i> Packs d'Abonnement</h1>
            <p class="text-muted">Gestion des formules groupées</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/subscription-manager/createPack" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau Pack
            </a>
            <a href="<?php echo APP_URL; ?>/subscription-manager/modules" class="btn btn-success">
                <i class="fas fa-puzzle-piece"></i> Voir les Modules
            </a>
        </div>
    </div>

    <div class="row">
        <?php if (!empty($data['packs'])): ?>
            <?php foreach ($data['packs'] as $pack): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow h-100 <?php echo $pack['is_featured'] ? 'border-primary' : ''; ?>">
                        <?php if ($pack['is_featured']): ?>
                            <div class="card-header bg-primary text-white text-center">
                                <i class="fas fa-star"></i> <strong>RECOMMANDÉ</strong>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h4 class="card-title"><?php echo htmlspecialchars($pack['pack_name']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($pack['description']); ?></p>

                            <div class="text-center my-4">
                                <h2 class="text-primary mb-0">
                                    <?php echo number_format($pack['price_monthly'], 2); ?>€
                                </h2>
                                <small class="text-muted">par mois</small>

                                <?php if ($pack['discount_percent'] > 0): ?>
                                    <div class="mt-2">
                                        <span class="badge badge-success">
                                            <i class="fas fa-tag"></i> Économie: <?php echo number_format($pack['discount_percent'], 0); ?>%
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($pack['price_yearly'] > 0): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            ou <?php echo number_format($pack['price_yearly'], 2); ?>€/an
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr>

                            <h6 class="text-muted"><i class="fas fa-list"></i> Modules Inclus:</h6>
                            <ul class="list-unstyled">
                                <?php
                                $totalModulesPrice = 0;
                                foreach ($pack['modules'] as $module):
                                    $totalModulesPrice += $module['price_monthly'];
                                ?>
                                    <li class="mb-2">
                                        <i class="fas <?php echo $module['icon']; ?> mr-2" style="color: <?php echo $module['color']; ?>"></i>
                                        <strong><?php echo htmlspecialchars($module['module_name']); ?></strong>
                                        <span class="text-muted float-right"><?php echo number_format($module['price_monthly'], 2); ?>€</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if ($totalModulesPrice > $pack['price_monthly']): ?>
                                <div class="alert alert-success text-center">
                                    <small>
                                        <strong>Vous économisez <?php echo number_format($totalModulesPrice - $pack['price_monthly'], 2); ?>€/mois</strong><br>
                                        (<?php echo number_format((($totalModulesPrice - $pack['price_monthly']) / $totalModulesPrice) * 100, 0); ?>% de réduction)
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="row">
                                <div class="col-6">
                                    <a href="<?php echo APP_URL; ?>/subscription-manager/createPack/<?php echo $pack['id']; ?>"
                                       class="btn btn-info btn-block btn-sm">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                </div>
                                <div class="col-6">
                                    <?php if ($pack['is_active']): ?>
                                        <span class="badge badge-success w-100 py-2">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary w-100 py-2">Inactif</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun pack enregistré.
                    <a href="<?php echo APP_URL; ?>/subscription-manager/createPack" class="alert-link">
                        Créer le premier pack
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
