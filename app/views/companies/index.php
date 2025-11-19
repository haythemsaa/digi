<?php require APP_PATH . '/views/includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-building"></i> Gestion des Entreprises</h2>
        <a href="<?= APP_URL ?>/companies/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Entreprise
        </a>
    </div>

    <?php flash('success'); ?>
    <?php flash('error'); ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Entreprises</h6>
                    <h3><?= count($data['companies']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Actives</h6>
                    <h3 class="text-success">
                        <?= count(array_filter($data['companies'], fn($c) => $c['status'] === 'active')) ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">En Essai</h6>
                    <h3 class="text-info">
                        <?= count(array_filter($data['companies'], fn($c) => $c['subscription_status'] === 'trial')) ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Suspendues</h6>
                    <h3 class="text-warning">
                        <?= count(array_filter($data['companies'], fn($c) => $c['status'] === 'suspended')) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Entreprise</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Statut Abonnement</th>
                            <th>Ressources</th>
                            <th>Statut</th>
                            <th>Date Création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['companies'] as $company): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($company['company_code']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($company['company_name']) ?></strong>
                                <?php if ($company['logo']): ?>
                                <br><small class="text-muted">Logo personnalisé</small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($company['email']) ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?= ucfirst($company['subscription_plan'] ?? 'starter') ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $subStatus = $company['subscription_status'] ?? 'trial';
                                $badgeClass = [
                                    'trial' => 'info',
                                    'active' => 'success',
                                    'suspended' => 'warning',
                                    'cancelled' => 'danger',
                                    'expired' => 'secondary'
                                ][$subStatus] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= ucfirst($subStatus) ?>
                                </span>
                                <?php if ($subStatus === 'trial' && $company['trial_ends_at']): ?>
                                <br><small class="text-muted">
                                    Expire: <?= date('d/m/Y', strtotime($company['trial_ends_at'])) ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <i class="fas fa-car"></i> <?= $company['total_vehicles'] ?? 0 ?>/<?= $company['max_vehicles'] ?><br>
                                    <i class="fas fa-users"></i> <?= $company['total_users'] ?? 0 ?>/<?= $company['max_users'] ?><br>
                                    <i class="fas fa-user"></i> <?= $company['total_drivers'] ?? 0 ?>/<?= $company['max_drivers'] ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'active' => 'success',
                                    'suspended' => 'warning',
                                    'inactive' => 'secondary'
                                ][$company['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst($company['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($company['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= APP_URL ?>/companies/view/<?= $company['id'] ?>"
                                       class="btn btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= APP_URL ?>/companies/edit/<?= $company['id'] ?>"
                                       class="btn btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= APP_URL ?>/companies/switchTo/<?= $company['id'] ?>"
                                       class="btn btn-success" title="Se connecter">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['companies'])): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Aucune entreprise trouvée
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/includes/footer.php'; ?>
