<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-box"></i> Détails du Colis: <?php echo htmlspecialchars($data['package']['package_number']); ?>
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/smart_delivery/packages" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la Liste
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Package Information -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-info-circle"></i> Informations du Colis</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Numéro de Colis:</th>
                            <td><strong class="text-primary"><?php echo htmlspecialchars($data['package']['package_number']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                <?php
                                $statusClasses = [
                                    'pending' => 'warning',
                                    'assigned' => 'info',
                                    'loaded' => 'primary',
                                    'in_transit' => 'primary',
                                    'delivered' => 'success',
                                    'failed' => 'danger',
                                    'returned' => 'secondary'
                                ];
                                $statusTexts = [
                                    'pending' => 'En Attente',
                                    'assigned' => 'Assigné',
                                    'loaded' => 'Chargé',
                                    'in_transit' => 'En Transit',
                                    'delivered' => 'Livré',
                                    'failed' => 'Échec',
                                    'returned' => 'Retourné'
                                ];
                                $badgeClass = $statusClasses[$data['package']['status']] ?? 'secondary';
                                $statusText = $statusTexts[$data['package']['status']] ?? $data['package']['status'];
                                ?>
                                <span class="badge badge-<?php echo $badgeClass; ?> p-2">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Priorité:</th>
                            <td>
                                <?php
                                $priorityClasses = [
                                    'low' => 'secondary',
                                    'normal' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ];
                                $priorityClass = $priorityClasses[$data['package']['priority']] ?? 'info';
                                ?>
                                <span class="badge badge-<?php echo $priorityClass; ?> p-2">
                                    <?php echo ucfirst($data['package']['priority']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Date de Création:</th>
                            <td><?php echo date('d/m/Y H:i', strtotime($data['package']['created_at'])); ?></td>
                        </tr>
                        <?php if ($data['package']['client_name']): ?>
                            <tr>
                                <th>Client:</th>
                                <td>
                                    <i class="fas fa-building"></i>
                                    <a href="<?php echo APP_URL; ?>/clients/view/<?php echo $data['package']['client_id']; ?>">
                                        <?php echo htmlspecialchars($data['package']['client_name']); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Dimensions & Weight -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-ruler-combined"></i> Dimensions et Poids</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-arrows-alt-h text-primary fa-2x mb-2"></i>
                                <h4 class="mb-0"><?php echo $data['package']['length']; ?></h4>
                                <small class="text-muted">cm (Longueur)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-arrows-alt-v text-success fa-2x mb-2"></i>
                                <h4 class="mb-0"><?php echo $data['package']['width']; ?></h4>
                                <small class="text-muted">cm (Largeur)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-arrows-alt text-info fa-2x mb-2"></i>
                                <h4 class="mb-0"><?php echo $data['package']['height']; ?></h4>
                                <small class="text-muted">cm (Hauteur)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-weight-hanging text-warning fa-2x mb-2"></i>
                                <h4 class="mb-0"><?php echo $data['package']['weight']; ?></h4>
                                <small class="text-muted">kg (Poids)</small>
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Volume:</th>
                            <td><strong><?php echo number_format($data['package']['volume'], 4); ?> m³</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Constraints -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0"><i class="fas fa-exclamation-triangle"></i> Contraintes</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-2">
                                <?php if ($data['package']['is_fragile']): ?>
                                    <i class="fas fa-wine-glass fa-2x text-danger mb-2"></i>
                                    <p class="mb-0"><strong class="text-danger">FRAGILE</strong></p>
                                <?php else: ?>
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <p class="mb-0 text-muted">Non fragile</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-2">
                                <?php if ($data['package']['is_stackable']): ?>
                                    <i class="fas fa-layer-group fa-2x text-success mb-2"></i>
                                    <p class="mb-0"><strong class="text-success">Empilable</strong></p>
                                <?php else: ?>
                                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                    <p class="mb-0 text-danger">Non empilable</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-2">
                                <?php if ($data['package']['rotation_allowed']): ?>
                                    <i class="fas fa-sync-alt fa-2x text-success mb-2"></i>
                                    <p class="mb-0"><strong class="text-success">Rotation OK</strong></p>
                                <?php else: ?>
                                    <i class="fas fa-ban fa-2x text-danger mb-2"></i>
                                    <p class="mb-0 text-danger">Pas de rotation</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-map-marker-alt"></i> Informations de Livraison</h6>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-map-marked-alt"></i> Adresse</h6>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($data['package']['delivery_address'])); ?></p>

                    <?php if ($data['package']['delivery_lat'] && $data['package']['delivery_lng']): ?>
                        <h6 class="mt-3"><i class="fas fa-crosshairs"></i> Coordonnées GPS</h6>
                        <p>
                            <strong>Latitude:</strong> <?php echo $data['package']['delivery_lat']; ?><br>
                            <strong>Longitude:</strong> <?php echo $data['package']['delivery_lng']; ?>
                        </p>

                        <!-- Map placeholder -->
                        <div id="delivery-map" style="height: 250px; background: #e9ecef; border-radius: 4px;"
                             data-lat="<?php echo $data['package']['delivery_lat']; ?>"
                             data-lng="<?php echo $data['package']['delivery_lng']; ?>">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="text-center">
                                    <i class="fas fa-map fa-3x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Carte interactive</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <h6><i class="fas fa-user"></i> Contact de Livraison</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="30%">Nom:</th>
                            <td><?php echo htmlspecialchars($data['package']['delivery_contact'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Téléphone:</th>
                            <td>
                                <?php if ($data['package']['delivery_phone']): ?>
                                    <a href="tel:<?php echo $data['package']['delivery_phone']; ?>">
                                        <i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($data['package']['delivery_phone']); ?>
                                    </a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>
                                <?php if ($data['package']['delivery_email']): ?>
                                    <a href="mailto:<?php echo $data['package']['delivery_email']; ?>">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($data['package']['delivery_email']); ?>
                                    </a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <?php if ($data['package']['time_window_start'] || $data['package']['time_window_end']): ?>
                        <hr>
                        <h6><i class="fas fa-clock"></i> Créneau Horaire</h6>
                        <p class="mb-0">
                            <strong>De:</strong> <?php echo $data['package']['time_window_start'] ?? 'N/A'; ?>
                            <strong>À:</strong> <?php echo $data['package']['time_window_end'] ?? 'N/A'; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes -->
            <?php if ($data['package']['notes']): ?>
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-sticky-note"></i> Notes</h6>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($data['package']['notes'])); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Delivery History -->
            <?php if ($data['package']['status'] == 'delivered' || $data['package']['status'] == 'failed'): ?>
                <div class="card shadow">
                    <div class="card-header bg-<?php echo $data['package']['status'] == 'delivered' ? 'success' : 'danger'; ?> text-white">
                        <h6 class="m-0">
                            <i class="fas fa-<?php echo $data['package']['status'] == 'delivered' ? 'check-circle' : 'times-circle'; ?>"></i>
                            Historique de Livraison
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($data['package']['delivered_at']): ?>
                            <p>
                                <strong>Date de livraison:</strong>
                                <?php echo date('d/m/Y H:i', strtotime($data['package']['delivered_at'])); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($data['package']['delivered_by']): ?>
                            <p>
                                <strong>Livré par:</strong>
                                <?php echo htmlspecialchars($data['package']['delivered_by']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($data['package']['delivery_notes']): ?>
                            <p>
                                <strong>Notes:</strong><br>
                                <?php echo nl2br(htmlspecialchars($data['package']['delivery_notes'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
