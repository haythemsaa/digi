<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-cube"></i> Plan de Chargement: <?php echo htmlspecialchars($data['route']['route_number']); ?>
            </h1>
            <p class="text-muted">Plan de chargement 3D optimisé par IA</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/smart_delivery/warehouseInterface/<?php echo $data['route']['id']; ?>"
               class="btn btn-success">
                <i class="fas fa-warehouse"></i> Interface Entrepôt
            </a>
            <a href="<?php echo APP_URL; ?>/smart_delivery/viewRoute/<?php echo $data['route']['id']; ?>"
               class="btn btn-info">
                <i class="fas fa-arrow-left"></i> Retour à la Route
            </a>
        </div>
    </div>

    <?php if ($data['loading_plan']): ?>
        <!-- Optimization Results -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow border-left-success">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Utilisation de l'Espace
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo number_format($data['loading_plan']['space_utilization'], 1); ?>%
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-pie fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow border-left-primary">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Colis Chargés
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $data['loading_plan']['fitted_count']; ?> / <?php echo $data['loading_plan']['total_count']; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow border-left-warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Poids Total
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo number_format($data['loading_plan']['total_weight'], 1); ?> kg
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-weight-hanging fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow border-left-info">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Temps de Chargement
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ~<?php echo ceil($data['loading_plan']['fitted_count'] * 2); ?> min
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3D Visualization -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h6 class="m-0"><i class="fas fa-cube"></i> Visualisation 3D du Chargement</h6>
                    </div>
                    <div class="card-body">
                        <div id="loading-3d-view" style="height: 500px; background: #f8f9fc; border: 2px solid #e3e6f0; border-radius: 4px; position: relative;">
                            <!-- 3D Canvas would go here -->
                            <canvas id="loading-canvas" style="width: 100%; height: 100%;"></canvas>

                            <!-- Legend -->
                            <div style="position: absolute; top: 10px; right: 10px; background: white; padding: 15px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <h6 class="mb-2"><i class="fas fa-info-circle"></i> Légende</h6>
                                <div class="mb-1">
                                    <span style="display: inline-block; width: 20px; height: 15px; background: #4e73df; border: 1px solid #000;"></span>
                                    Colis Normal
                                </div>
                                <div class="mb-1">
                                    <span style="display: inline-block; width: 20px; height: 15px; background: #e74a3b; border: 1px solid #000;"></span>
                                    Fragile
                                </div>
                                <div class="mb-1">
                                    <span style="display: inline-block; width: 20px; height: 15px; background: #f6c23e; border: 1px solid #000;"></span>
                                    Haute Priorité
                                </div>
                                <div>
                                    <span style="display: inline-block; width: 20px; height: 15px; background: #1cc88a; border: 1px solid #000;"></span>
                                    Non Empilable
                                </div>
                            </div>

                            <!-- Controls -->
                            <div style="position: absolute; bottom: 10px; left: 10px; background: white; padding: 10px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <button class="btn btn-sm btn-primary" id="rotate-left">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" id="rotate-right">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <button class="btn btn-sm btn-info" id="view-top">
                                    Vue Dessus
                                </button>
                                <button class="btn btn-sm btn-info" id="view-side">
                                    Vue Côté
                                </button>
                                <button class="btn btn-sm btn-info" id="view-iso">
                                    Vue ISO
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-lightbulb"></i>
                            <strong>Optimisation IA:</strong>
                            L'algorithme de Bin Packing 3D a placé les colis pour maximiser l'utilisation de l'espace
                            tout en respectant les contraintes (LIFO, fragilité, empilement, rotation).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Instructions List -->
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0">
                            <i class="fas fa-list-ol"></i> Séquence de Chargement
                            (<?php echo count($data['loading_plan']['instructions']); ?> étapes)
                        </h6>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php foreach ($data['loading_plan']['instructions'] as $instruction): ?>
                            <div class="card mb-2 <?php echo $instruction['is_completed'] ? 'border-success' : ''; ?>">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-start">
                                        <div class="mr-2">
                                            <?php if ($instruction['is_completed']): ?>
                                                <i class="fas fa-check-circle text-success fa-lg"></i>
                                            <?php else: ?>
                                                <span class="badge badge-primary"><?php echo $instruction['load_sequence']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong><?php echo htmlspecialchars($instruction['package_number']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo $instruction['weight']; ?> kg
                                                - <?php echo $instruction['length']; ?>×<?php echo $instruction['width']; ?>×<?php echo $instruction['height']; ?> cm
                                            </small>
                                            <br>
                                            <small>
                                                <i class="fas fa-crosshairs text-info"></i>
                                                Position: X:<?php echo $instruction['position_x']; ?>
                                                Y:<?php echo $instruction['position_y']; ?>
                                                Z:<?php echo $instruction['position_z']; ?>
                                            </small>

                                            <?php if ($instruction['warnings']): ?>
                                                <br>
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <?php echo htmlspecialchars($instruction['warnings']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Instructions Table -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-table"></i> Instructions Détaillées</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Colis</th>
                                        <th>Dimensions (L×l×h)</th>
                                        <th>Poids</th>
                                        <th>Position (X, Y, Z)</th>
                                        <th>Rotation</th>
                                        <th>Contraintes</th>
                                        <th>Instructions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['loading_plan']['instructions'] as $instruction): ?>
                                        <tr class="<?php echo $instruction['is_completed'] ? 'table-success' : ''; ?>">
                                            <td class="text-center">
                                                <strong><?php echo $instruction['load_sequence']; ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($instruction['package_number']); ?></td>
                                            <td>
                                                <?php echo $instruction['length']; ?>×<?php echo $instruction['width']; ?>×<?php echo $instruction['height']; ?> cm
                                            </td>
                                            <td><?php echo $instruction['weight']; ?> kg</td>
                                            <td>
                                                <small>
                                                    X: <?php echo $instruction['position_x']; ?> cm<br>
                                                    Y: <?php echo $instruction['position_y']; ?> cm<br>
                                                    Z: <?php echo $instruction['position_z']; ?> cm
                                                </small>
                                            </td>
                                            <td>
                                                <?php
                                                $rotationText = [
                                                    'normal' => 'Normal',
                                                    'rotated_90' => '90°',
                                                    'rotated_180' => '180°',
                                                    'rotated_270' => '270°'
                                                ];
                                                echo $rotationText[$instruction['rotation']] ?? $instruction['rotation'];
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($instruction['is_fragile']): ?>
                                                    <span class="badge badge-danger">FRAGILE</span>
                                                <?php endif; ?>
                                                <?php if (!$instruction['is_stackable']): ?>
                                                    <span class="badge badge-warning">Non empilable</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo nl2br(htmlspecialchars($instruction['instruction_text'] ?? '')); ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- No Loading Plan -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-cube fa-4x text-muted mb-3"></i>
                        <h4>Aucun Plan de Chargement</h4>
                        <p class="text-muted">
                            Le plan de chargement n'a pas encore été généré pour cette route.
                        </p>
                        <a href="<?php echo APP_URL; ?>/smart_delivery/optimizeLoading/<?php echo $data['route']['id']; ?>"
                           class="btn btn-success btn-lg">
                            <i class="fas fa-brain"></i> Optimiser le Chargement Maintenant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Simple 2D representation for loading visualization
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('loading-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;

    let rotation = 0;

    function drawLoadingPlan(angle = 0) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Draw vehicle outline
        const vehicleWidth = 400;
        const vehicleHeight = 300;
        const startX = (canvas.width - vehicleWidth) / 2;
        const startY = (canvas.height - vehicleHeight) / 2;

        ctx.strokeStyle = '#333';
        ctx.lineWidth = 3;
        ctx.strokeRect(startX, startY, vehicleWidth, vehicleHeight);

        // Draw packages
        <?php if ($data['loading_plan'] && !empty($data['loading_plan']['instructions'])): ?>
            const packages = <?php echo json_encode($data['loading_plan']['instructions']); ?>;

            packages.forEach((pkg, index) => {
                // Scale positions to canvas
                const x = startX + (pkg.position_x / 500 * vehicleWidth);
                const y = startY + (pkg.position_y / 400 * vehicleHeight);
                const w = (pkg.length / 500 * vehicleWidth);
                const h = (pkg.width / 400 * vehicleHeight);

                // Color based on properties
                if (pkg.is_fragile) {
                    ctx.fillStyle = '#e74a3b';
                } else if (pkg.priority === 'high' || pkg.priority === 'urgent') {
                    ctx.fillStyle = '#f6c23e';
                } else if (!pkg.is_stackable) {
                    ctx.fillStyle = '#1cc88a';
                } else {
                    ctx.fillStyle = '#4e73df';
                }

                ctx.globalAlpha = 0.7;
                ctx.fillRect(x, y, w, h);
                ctx.globalAlpha = 1.0;

                ctx.strokeStyle = '#000';
                ctx.lineWidth = 1;
                ctx.strokeRect(x, y, w, h);

                // Draw sequence number
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 14px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(pkg.load_sequence, x + w/2, y + h/2 + 5);
            });
        <?php endif; ?>
    }

    drawLoadingPlan();

    // View controls
    document.getElementById('rotate-left')?.addEventListener('click', () => {
        rotation -= 15;
        drawLoadingPlan(rotation);
    });

    document.getElementById('rotate-right')?.addEventListener('click', () => {
        rotation += 15;
        drawLoadingPlan(rotation);
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
