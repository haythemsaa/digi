<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-box"></i> Gestion des Colis</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/smart_delivery/addPackage" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau colis
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <label class="mr-2">Filtre par statut:</label>
                        <select name="status" class="form-control mr-2">
                            <option value="">Tous</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>En attente</option>
                            <option value="assigned" <?php echo (isset($_GET['status']) && $_GET['status'] == 'assigned') ? 'selected' : ''; ?>>Assigné</option>
                            <option value="loaded" <?php echo (isset($_GET['status']) && $_GET['status'] == 'loaded') ? 'selected' : ''; ?>>Chargé</option>
                            <option value="in_transit" <?php echo (isset($_GET['status']) && $_GET['status'] == 'in_transit') ? 'selected' : ''; ?>>En transit</option>
                            <option value="delivered" <?php echo (isset($_GET['status']) && $_GET['status'] == 'delivered') ? 'selected' : ''; ?>>Livré</option>
                            <option value="failed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'failed') ? 'selected' : ''; ?>>Échec</option>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Packages Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <?php if (!empty($data['packages'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="packagesTable">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Client</th>
                                        <th>Description</th>
                                        <th>Dimensions</th>
                                        <th>Poids</th>
                                        <th>Adresse</th>
                                        <th>Priorité</th>
                                        <th>Contraintes</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['packages'] as $pkg): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($pkg['package_number']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($pkg['client_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($pkg['description']); ?></td>
                                            <td>
                                                <small>
                                                    <?php echo $pkg['length']; ?> ×
                                                    <?php echo $pkg['width']; ?> ×
                                                    <?php echo $pkg['height']; ?> cm<br>
                                                    <span class="text-muted"><?php echo number_format($pkg['volume'], 3); ?> m³</span>
                                                </small>
                                            </td>
                                            <td><?php echo $pkg['weight']; ?> kg</td>
                                            <td>
                                                <small><?php echo substr(htmlspecialchars($pkg['delivery_address']), 0, 40); ?>...</small>
                                            </td>
                                            <td>
                                                <?php
                                                $priorityClasses = [
                                                    'low' => 'secondary',
                                                    'normal' => 'info',
                                                    'high' => 'warning',
                                                    'urgent' => 'danger'
                                                ];
                                                $badgeClass = $priorityClasses[$pkg['priority']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo ucfirst($pkg['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($pkg['is_fragile']): ?>
                                                    <span class="badge badge-danger" title="Fragile">
                                                        <i class="fas fa-wine-glass-alt"></i>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!$pkg['is_stackable']): ?>
                                                    <span class="badge badge-warning" title="Non empilable">
                                                        <i class="fas fa-ban"></i>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!$pkg['rotation_allowed']): ?>
                                                    <span class="badge badge-info" title="Pas de rotation">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
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
                                                $badgeClass = $statusClasses[$pkg['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $badgeClass; ?>">
                                                    <?php echo ucfirst($pkg['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/smart_delivery/viewPackage/<?php echo $pkg['id']; ?>"
                                                   class="btn btn-sm btn-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun colis trouvé. <a href="<?php echo APP_URL; ?>/smart_delivery/addPackage">Créer un nouveau colis</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#packagesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "desc"]]
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
