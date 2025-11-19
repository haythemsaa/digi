<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-exclamation-triangle"></i> Alertes Carburant</h1>
            <p class="text-muted">Gestion des alertes et anomalies</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?php echo APP_URL; ?>/fuel" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-bell"></i> Alertes Actives
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($data['alerts'])): ?>
                <div class="row">
                    <?php foreach ($data['alerts'] as $alert): ?>
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-<?php echo $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'high' ? 'warning' : 'info'); ?> shadow-sm">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="alert-heading">
                                            <i class="fas fa-<?php echo $alert['severity'] === 'critical' ? 'exclamation-circle' : 'exclamation-triangle'; ?>"></i>
                                            <?php echo htmlspecialchars($alert['title']); ?>
                                        </h6>
                                        <p class="mb-2"><?php echo htmlspecialchars($alert['description']); ?></p>

                                        <?php if ($alert['vehicle_id']): ?>
                                            <small>
                                                <i class="fas fa-car"></i>
                                                <strong>Véhicule:</strong> <?php echo htmlspecialchars($alert['registration_number']); ?>
                                            </small>
                                            <br>
                                        <?php endif; ?>

                                        <?php if ($alert['driver_id']): ?>
                                            <small>
                                                <i class="fas fa-user"></i>
                                                <strong>Conducteur:</strong> <?php echo htmlspecialchars($alert['driver_name']); ?>
                                            </small>
                                            <br>
                                        <?php endif; ?>

                                        <?php if ($alert['threshold_value'] && $alert['actual_value']): ?>
                                            <small>
                                                <i class="fas fa-chart-line"></i>
                                                <strong>Seuil:</strong> <?php echo $alert['threshold_value']; ?> |
                                                <strong>Valeur:</strong> <?php echo $alert['actual_value']; ?>
                                            </small>
                                            <br>
                                        <?php endif; ?>

                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($alert['created_at'])); ?>
                                        </small>
                                    </div>

                                    <div>
                                        <form method="POST" action="<?php echo APP_URL; ?>/fuel/resolveAlert/<?php echo $alert['id']; ?>" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-success" title="Résoudre">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5>Aucune alerte active</h5>
                    <p class="text-muted">Toutes les alertes ont été résolues ou aucune anomalie détectée.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Types Information -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-info-circle"></i> Types d'Alertes</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-exclamation-circle text-danger"></i> Alertes Critiques</h6>
                            <ul>
                                <li>Consommation anormalement élevée</li>
                                <li>Ravitaillement suspect (hors horaires, quantité excessive)</li>
                                <li>Dépassement de limite de carte carburant</li>
                                <li>Transaction non autorisée</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-exclamation-triangle text-warning"></i> Alertes Moyennes</h6>
                            <ul>
                                <li>Variation de prix inhabituelle</li>
                                <li>Écart de consommation significatif</li>
                                <li>Carte proche de l'expiration</li>
                                <li>Limite mensuelle presque atteinte</li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-success mt-3">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Conseil:</strong> Traitez les alertes critiques en priorité et résolvez-les rapidement
                        pour maintenir un contrôle optimal des coûts carburant.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
