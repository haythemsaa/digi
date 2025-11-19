<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-dollar-sign"></i> Prix du Carburant</h1>
            <p class="text-muted">Suivi des prix actuels et historique</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Current Prices -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tag"></i> Prix Actuels
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($data['current_prices'])): ?>
                <div class="row">
                    <?php foreach ($data['current_prices'] as $price): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <h5 class="text-uppercase">
                                        <?php
                                        $fuelTypes = [
                                            'diesel' => 'Diesel',
                                            'gasoline' => 'Essence',
                                            'lpg' => 'GPL',
                                            'cng' => 'GNC',
                                            'electric' => 'Électrique'
                                        ];
                                        echo $fuelTypes[$price['fuel_type']] ?? strtoupper($price['fuel_type']);
                                        ?>
                                    </h5>
                                    <h2 class="text-primary mb-2">
                                        <?php echo number_format($price['price_per_liter'], 3); ?> TND
                                    </h2>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i>
                                        En vigueur depuis: <?php echo date('d/m/Y', strtotime($price['effective_date'])); ?>
                                    </small>
                                    <?php if ($price['provider']): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-gas-pump"></i>
                                            <?php echo htmlspecialchars($price['provider']); ?>
                                        </small>
                                    <?php endif; ?>
                                    <?php if ($price['is_official_price']): ?>
                                        <br>
                                        <span class="badge badge-success mt-2">Prix Officiel</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucun prix enregistré.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Price Information -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-info-circle"></i> Informations</h6>
                </div>
                <div class="card-body">
                    <h6>À propos des prix du carburant en Tunisie:</h6>
                    <ul>
                        <li>Les prix sont fixés par le gouvernement tunisien</li>
                        <li>Les révisions ont lieu périodiquement en fonction du marché international</li>
                        <li>Les prix peuvent varier légèrement selon les stations et fournisseurs</li>
                        <li>Le système suit automatiquement les prix lors des transactions</li>
                    </ul>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> Les prix affichés sont les derniers prix enregistrés dans le système.
                        Assurez-vous de les mettre à jour régulièrement pour un suivi précis des coûts.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
