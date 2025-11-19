<?php require_once '../app/views/includes/header.php'; ?>

<style>
.loading-step {
    border-left: 5px solid #007bff;
    background: #f8f9fc;
    transition: all 0.3s;
}
.loading-step.completed {
    border-left-color: #28a745;
    background: #d4edda;
}
.loading-step.active {
    border-left-color: #ffc107;
    background: #fff3cd;
    box-shadow: 0 0 20px rgba(255,193,7,0.3);
}
.position-box {
    background: #e3f2fd;
    border: 2px solid #2196f3;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
}
.warning-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 10px;
    margin: 10px 0;
}
.progress-container {
    position: sticky;
    top: 20px;
    z-index: 100;
}
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-warehouse"></i> Interface Magasinier
            </h1>
            <p class="text-muted">Route: <strong><?php echo htmlspecialchars($data['route']['route_number']); ?></strong></p>
        </div>
    </div>

    <?php if ($data['loading_plan'] && !empty($data['loading_plan']['instructions'])): ?>
        <!-- Progress Bar -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow progress-container">
                    <div class="card-body">
                        <h5 class="mb-3">Progression du Chargement</h5>
                        <?php
                        $totalInstructions = count($data['loading_plan']['instructions']);
                        $completedInstructions = 0;
                        foreach ($data['loading_plan']['instructions'] as $instr) {
                            if ($instr['is_completed']) $completedInstructions++;
                        }
                        $progressPercent = ($totalInstructions > 0) ? ($completedInstructions / $totalInstructions) * 100 : 0;
                        ?>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar"
                                 style="width: <?php echo $progressPercent; ?>%"
                                 aria-valuenow="<?php echo $progressPercent; ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                <?php echo round($progressPercent, 1); ?>% - <?php echo $completedInstructions; ?> / <?php echo $totalInstructions; ?> colis
                            </div>
                        </div>
                        <p class="mt-2 mb-0">
                            <strong>Temps estimé restant:</strong>
                            <?php
                            $avgTimePerPackage = 3; // 3 minutes average
                            $remainingPackages = $totalInstructions - $completedInstructions;
                            $estimatedMinutes = $remainingPackages * $avgTimePerPackage;
                            echo floor($estimatedMinutes / 60) . 'h ' . ($estimatedMinutes % 60) . 'min';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Instructions -->
        <div class="row">
            <div class="col-md-12">
                <?php
                $currentStep = 0;
                $nextStepFound = false;
                foreach ($data['loading_plan']['instructions'] as $index => $instruction):
                    $stepNumber = $index + 1;
                    $isCompleted = $instruction['is_completed'];
                    $isActive = !$nextStepFound && !$isCompleted;
                    if ($isActive) $nextStepFound = true;

                    $cardClass = 'loading-step';
                    if ($isCompleted) $cardClass .= ' completed';
                    if ($isActive) $cardClass .= ' active';
                ?>
                    <div class="card shadow mb-3 <?php echo $cardClass; ?>" id="step-<?php echo $stepNumber; ?>">
                        <div class="card-header <?php echo $isCompleted ? 'bg-success text-white' : ($isActive ? 'bg-warning' : 'bg-light'); ?>">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-0">
                                        <?php if ($isCompleted): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php elseif ($isActive): ?>
                                            <i class="fas fa-arrow-circle-right"></i>
                                        <?php else: ?>
                                            <i class="fas fa-circle"></i>
                                        <?php endif; ?>
                                        Étape <?php echo $stepNumber; ?> / <?php echo $totalInstructions; ?>
                                    </h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <?php if (!$isCompleted && $isActive): ?>
                                        <button type="button" class="btn btn-success btn-sm complete-step"
                                                data-instruction-id="<?php echo $instruction['id']; ?>"
                                                data-step="<?php echo $stepNumber; ?>">
                                            <i class="fas fa-check"></i> Marquer comme chargé
                                        </button>
                                    <?php elseif ($isCompleted): ?>
                                        <span class="badge badge-light badge-lg">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('H:i', strtotime($instruction['completed_at'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <!-- Package Info -->
                                <div class="col-md-4">
                                    <h6 class="text-primary"><i class="fas fa-box"></i> Informations du Colis</h6>
                                    <p class="mb-1">
                                        <strong>Numéro:</strong>
                                        <span class="badge badge-primary badge-lg">
                                            <?php echo htmlspecialchars($instruction['package_number']); ?>
                                        </span>
                                    </p>
                                    <p class="mb-1"><strong>Description:</strong> <?php echo htmlspecialchars($instruction['description']); ?></p>
                                    <p class="mb-1"><strong>Poids:</strong> <span class="badge badge-info"><?php echo $instruction['weight']; ?> kg</span></p>
                                    <p class="mb-1">
                                        <strong>Dimensions:</strong>
                                        <?php echo $instruction['length']; ?> ×
                                        <?php echo $instruction['width']; ?> ×
                                        <?php echo $instruction['height']; ?> cm
                                    </p>
                                </div>

                                <!-- Position 3D -->
                                <div class="col-md-4">
                                    <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Position à Placer</h6>
                                    <div class="position-box">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="mb-2"><small class="text-muted">X (Gauche/Droite)</small></div>
                                                <h4 class="text-primary mb-0"><?php echo number_format($instruction['position_x'], 0); ?> cm</h4>
                                                <small class="text-muted">du bord gauche</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="mb-2"><small class="text-muted">Y (Avant/Arrière)</small></div>
                                                <h4 class="text-success mb-0"><?php echo number_format($instruction['position_y'], 0); ?> cm</h4>
                                                <small class="text-muted">du devant</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="mb-2"><small class="text-muted">Z (Hauteur)</small></div>
                                                <h4 class="text-danger mb-0"><?php echo number_format($instruction['position_z'], 0); ?> cm</h4>
                                                <small class="text-muted">du sol</small>
                                            </div>
                                        </div>

                                        <?php if ($instruction['placed_on_package_id']): ?>
                                            <div class="alert alert-info mt-2 mb-0">
                                                <i class="fas fa-info-circle"></i>
                                                <small>À placer sur le colis précédent</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($instruction['rotation'] != 'normal'): ?>
                                        <div class="alert alert-warning mt-2">
                                            <i class="fas fa-sync-alt"></i>
                                            <strong>Rotation:</strong> <?php echo str_replace('_', ' ', $instruction['rotation']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Instructions & Warnings -->
                                <div class="col-md-4">
                                    <h6 class="text-primary"><i class="fas fa-clipboard-list"></i> Instructions</h6>
                                    <div class="alert alert-primary">
                                        <?php echo nl2br(htmlspecialchars($instruction['instruction_text'])); ?>
                                    </div>

                                    <?php if ($instruction['warnings']): ?>
                                        <h6 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Avertissements</h6>
                                        <div class="warning-box">
                                            <?php
                                            $warnings = explode('.', $instruction['warnings']);
                                            foreach ($warnings as $warning):
                                                $warning = trim($warning);
                                                if (!empty($warning)):
                                            ?>
                                                    <div class="mb-1">
                                                        <?php if (stripos($warning, 'FRAGILE') !== false): ?>
                                                            <i class="fas fa-wine-glass-alt text-danger"></i>
                                                        <?php elseif (stripos($warning, 'HEAVY') !== false || stripos($warning, 'LOURD') !== false): ?>
                                                            <i class="fas fa-weight-hanging text-warning"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-exclamation-circle text-warning"></i>
                                                        <?php endif; ?>
                                                        <strong><?php echo htmlspecialchars($warning); ?></strong>
                                                    </div>
                                            <?php
                                                endif;
                                            endforeach;
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($isCompleted): ?>
                                <div class="alert alert-success mt-3 mb-0">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Chargé avec succès</strong> le
                                    <?php echo date('d/m/Y à H:i', strtotime($instruction['completed_at'])); ?>
                                    par <?php echo htmlspecialchars($instruction['completed_by_name'] ?? 'Magasinier'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($completedInstructions == $totalInstructions): ?>
                    <div class="card shadow border-left-success">
                        <div class="card-body bg-success text-white text-center py-5">
                            <h2><i class="fas fa-check-circle"></i> Chargement Terminé !</h2>
                            <p class="lead mb-4">Tous les colis ont été chargés avec succès.</p>
                            <a href="<?php echo APP_URL; ?>/smart_delivery/viewRoute/<?php echo $data['route']['id']; ?>"
                               class="btn btn-light btn-lg">
                                <i class="fas fa-arrow-left"></i> Retour à la route
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Aucun plan de chargement disponible pour cette route.
                    Veuillez d'abord <a href="<?php echo APP_URL; ?>/smart_delivery/viewRoute/<?php echo $data['route']['id']; ?>">optimiser le chargement</a>.
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Auto-scroll to active step
    const activeStep = $('.loading-step.active');
    if (activeStep.length > 0) {
        $('html, body').animate({
            scrollTop: activeStep.offset().top - 100
        }, 500);
    }

    // Complete step handler
    $('.complete-step').click(function() {
        const btn = $(this);
        const instructionId = btn.data('instruction-id');
        const stepNumber = btn.data('step');

        if (!confirm('Confirmer que ce colis a été chargé correctement ?')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Validation...');

        $.ajax({
            url: '<?php echo APP_URL; ?>/smart_delivery/completeInstruction',
            type: 'POST',
            data: { instruction_id: instructionId },
            success: function(response) {
                if (response.success) {
                    // Reload page to show updated progress
                    location.reload();
                } else {
                    alert('Erreur: ' + (response.error || 'Unknown error'));
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i> Marquer comme chargé');
                }
            },
            error: function() {
                alert('Erreur de connexion');
                btn.prop('disabled', false).html('<i class="fas fa-check"></i> Marquer comme chargé');
            }
        });
    });
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
