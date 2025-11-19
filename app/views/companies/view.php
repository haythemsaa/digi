<?php require APP_PATH . '/views/includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-building"></i>
            <?= htmlspecialchars($data['company']['company_name']) ?>
            <?php
            $statusClass = [
                'active' => 'success',
                'suspended' => 'warning',
                'inactive' => 'secondary'
            ][$data['company']['status']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $statusClass ?> fs-6">
                <?= ucfirst($data['company']['status']) ?>
            </span>
        </h2>
        <div>
            <a href="<?= APP_URL ?>/companies/edit/<?= $data['company']['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="<?= APP_URL ?>/companies/switchTo/<?= $data['company']['id'] ?>" class="btn btn-success">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </a>
            <a href="<?= APP_URL ?>/companies" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php flash('success'); ?>
    <?php flash('error'); ?>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Utilisateurs</h6>
                            <h3 class="mb-0">
                                <?= $data['stats']['total_users'] ?? 0 ?> / <?= $data['company']['max_users'] ?>
                            </h3>
                        </div>
                        <div class="fs-1 text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <?php
                    $userPercent = $data['company']['max_users'] > 0
                        ? ($data['stats']['total_users'] ?? 0) / $data['company']['max_users'] * 100
                        : 0;
                    ?>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar" style="width: <?= min($userPercent, 100) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Véhicules</h6>
                            <h3 class="mb-0">
                                <?= $data['stats']['total_vehicles'] ?? 0 ?> / <?= $data['company']['max_vehicles'] ?>
                            </h3>
                        </div>
                        <div class="fs-1 text-success">
                            <i class="fas fa-car"></i>
                        </div>
                    </div>
                    <?php
                    $vehiclePercent = $data['company']['max_vehicles'] > 0
                        ? ($data['stats']['total_vehicles'] ?? 0) / $data['company']['max_vehicles'] * 100
                        : 0;
                    ?>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: <?= min($vehiclePercent, 100) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Chauffeurs</h6>
                            <h3 class="mb-0">
                                <?= $data['stats']['total_drivers'] ?? 0 ?> / <?= $data['company']['max_drivers'] ?>
                            </h3>
                        </div>
                        <div class="fs-1 text-info">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <?php
                    $driverPercent = $data['company']['max_drivers'] > 0
                        ? ($data['stats']['total_drivers'] ?? 0) / $data['company']['max_drivers'] * 100
                        : 0;
                    ?>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar bg-info" style="width: <?= min($driverPercent, 100) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Abonnement</h6>
                            <h3 class="mb-0">
                                <?= ucfirst($data['company']['subscription_plan'] ?? 'starter') ?>
                            </h3>
                        </div>
                        <div class="fs-1 text-warning">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                    <?php
                    $subStatus = $data['company']['subscription_status'] ?? 'trial';
                    $subBadgeClass = [
                        'trial' => 'info',
                        'active' => 'success',
                        'suspended' => 'warning',
                        'cancelled' => 'danger',
                        'expired' => 'secondary'
                    ][$subStatus] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $subBadgeClass ?> mt-2">
                        <?= ucfirst($subStatus) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations de l'entreprise</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Code Entreprise</label>
                            <div><strong><?= htmlspecialchars($data['company']['company_code']) ?></strong></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Raison Sociale</label>
                            <div><?= htmlspecialchars($data['company']['legal_name'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Type d'entreprise</label>
                            <div><?= strtoupper($data['company']['business_type'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Matricule Fiscal</label>
                            <div><?= htmlspecialchars($data['company']['tax_id'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Registre Commerce</label>
                            <div><?= htmlspecialchars($data['company']['registration_number'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email</label>
                            <div>
                                <a href="mailto:<?= htmlspecialchars($data['company']['email']) ?>">
                                    <?= htmlspecialchars($data['company']['email']) ?>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Téléphone</label>
                            <div><?= htmlspecialchars($data['company']['phone'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Site Web</label>
                            <div>
                                <?php if ($data['company']['website']): ?>
                                    <a href="<?= htmlspecialchars($data['company']['website']) ?>" target="_blank">
                                        <?= htmlspecialchars($data['company']['website']) ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="text-muted small">Adresse</label>
                            <div>
                                <?= htmlspecialchars($data['company']['address'] ?? '-') ?><br>
                                <?= htmlspecialchars($data['company']['city'] ?? '') ?>
                                <?= htmlspecialchars($data['company']['postal_code'] ?? '') ?><br>
                                <?= htmlspecialchars($data['company']['country'] ?? '') ?>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Date de Création</label>
                            <div><?= date('d/m/Y H:i', strtotime($data['company']['created_at'])) ?></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Dernière Mise à jour</label>
                            <div><?= date('d/m/Y H:i', strtotime($data['company']['updated_at'] ?? $data['company']['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Utilisateurs (<?= count($data['users']) ?>)</h5>
                    <a href="<?= APP_URL ?>/companies/switchTo/<?= $data['company']['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Ajouter Utilisateur
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['users'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Dernière Connexion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['users'] as $user): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                        <?php if ($user['is_super_admin']): ?>
                                        <span class="badge bg-danger ms-1">Super Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><span class="badge bg-info"><?= ucfirst($user['role']) ?></span></td>
                                    <td>
                                        <?php $userStatusClass = $user['status'] === 'active' ? 'success' : 'secondary'; ?>
                                        <span class="badge bg-<?= $userStatusClass ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center py-3">Aucun utilisateur</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Subscription -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Abonnement</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Plan Actuel</label>
                        <div class="fs-5">
                            <i class="fas fa-crown text-warning"></i>
                            <strong><?= ucfirst($data['company']['subscription_plan'] ?? 'starter') ?></strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Statut</label>
                        <div>
                            <span class="badge bg-<?= $subBadgeClass ?>">
                                <?= ucfirst($subStatus) ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($subStatus === 'trial' && $data['company']['trial_ends_at']): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Fin de l'essai</label>
                        <div><?= date('d/m/Y', strtotime($data['company']['trial_ends_at'])) ?></div>
                        <?php
                        $daysRemaining = ceil((strtotime($data['company']['trial_ends_at']) - time()) / 86400);
                        ?>
                        <small class="text-warning">
                            <i class="fas fa-clock"></i> <?= $daysRemaining ?> jour(s) restant(s)
                        </small>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="text-muted small">Devise</label>
                        <div><?= $data['company']['currency'] ?? 'TND' ?></div>
                    </div>

                    <form method="POST" action="<?= APP_URL ?>/companies/updateSubscription/<?= $data['company']['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label small">Changer le Statut</label>
                            <select name="subscription_status" class="form-select form-select-sm">
                                <option value="trial" <?= $subStatus === 'trial' ? 'selected' : '' ?>>Trial</option>
                                <option value="active" <?= $subStatus === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="suspended" <?= $subStatus === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                <option value="cancelled" <?= $subStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="expired" <?= $subStatus === 'expired' ? 'selected' : '' ?>>Expired</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-save"></i> Mettre à jour
                        </button>
                    </form>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>/companies/updateStatus/<?= $data['company']['id'] ?>" class="mb-2">
                        <select name="status" class="form-select form-select-sm mb-2">
                            <option value="active" <?= $data['company']['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= $data['company']['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="inactive" <?= $data['company']['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                            Changer le Statut
                        </button>
                    </form>

                    <form method="POST" action="<?= APP_URL ?>/companies/extendTrial/<?= $data['company']['id'] ?>" class="mb-2">
                        <input type="number" name="days" class="form-control form-control-sm mb-2"
                               placeholder="Jours" value="30" min="1">
                        <button type="submit" class="btn btn-sm btn-outline-warning w-100">
                            Prolonger l'Essai
                        </button>
                    </form>

                    <hr>

                    <form method="POST" action="<?= APP_URL ?>/companies/delete/<?= $data['company']['id'] ?>"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ? Cette action est irréversible.');">
                        <button type="submit" class="btn btn-sm btn-danger w-100">
                            <i class="fas fa-trash"></i> Supprimer l'Entreprise
                        </button>
                    </form>
                </div>
            </div>

            <!-- Branding -->
            <?php if ($data['company']['logo'] || $data['company']['primary_color']): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Personnalisation</h5>
                </div>
                <div class="card-body">
                    <?php if ($data['company']['logo']): ?>
                    <div class="mb-3 text-center">
                        <img src="<?= htmlspecialchars($data['company']['logo']) ?>"
                             alt="Logo" class="img-fluid" style="max-height: 100px;">
                    </div>
                    <?php endif; ?>

                    <?php if ($data['company']['primary_color']): ?>
                    <div class="mb-2">
                        <label class="text-muted small">Couleur Primaire</label>
                        <div class="d-flex align-items-center">
                            <div style="width: 30px; height: 30px; background-color: <?= htmlspecialchars($data['company']['primary_color']) ?>; border: 1px solid #ddd; border-radius: 4px;"></div>
                            <span class="ms-2"><?= htmlspecialchars($data['company']['primary_color']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-2">
                        <label class="text-muted small">Timezone</label>
                        <div><?= htmlspecialchars($data['company']['timezone'] ?? 'Africa/Tunis') ?></div>
                    </div>

                    <div>
                        <label class="text-muted small">Langue</label>
                        <div><?= strtoupper($data['company']['language'] ?? 'fr') ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/includes/footer.php'; ?>
