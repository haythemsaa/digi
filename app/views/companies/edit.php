<?php require APP_PATH . '/views/includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit"></i> Modifier l'Entreprise: <?= htmlspecialchars($data['company']['company_name']) ?></h2>
        <div>
            <a href="<?= APP_URL ?>/companies/view/<?= $data['company']['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-eye"></i> Voir Détails
            </a>
            <a href="<?= APP_URL ?>/companies" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php flash('error'); ?>
    <?php flash('success'); ?>

    <form method="POST" action="<?= APP_URL ?>/companies/edit/<?= $data['company']['id'] ?>" enctype="multipart/form-data">
        <div class="row">
            <!-- Informations de base -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations de Base</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['company_name']) ?>" required>
                                <?php if (isset($data['errors']['company_name'])): ?>
                                    <div class="text-danger small"><?= $data['errors']['company_name'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code Entreprise</label>
                                <input type="text" name="company_code" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['company_code']) ?>" readonly>
                                <small class="text-muted">Le code ne peut pas être modifié</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Raison Sociale</label>
                                <input type="text" name="legal_name" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['legal_name'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type d'entreprise</label>
                                <select name="business_type" class="form-select">
                                    <option value="">Sélectionner...</option>
                                    <option value="sarl" <?= ($data['company']['business_type'] ?? '') === 'sarl' ? 'selected' : '' ?>>SARL</option>
                                    <option value="sa" <?= ($data['company']['business_type'] ?? '') === 'sa' ? 'selected' : '' ?>>SA</option>
                                    <option value="suarl" <?= ($data['company']['business_type'] ?? '') === 'suarl' ? 'selected' : '' ?>>SUARL</option>
                                    <option value="individual" <?= ($data['company']['business_type'] ?? '') === 'individual' ? 'selected' : '' ?>>Individuelle</option>
                                    <option value="other" <?= ($data['company']['business_type'] ?? '') === 'other' ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Matricule Fiscal</label>
                                <input type="text" name="tax_id" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['tax_id'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registre Commerce</label>
                                <input type="text" name="registration_number" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['registration_number'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Coordonnées</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['email']) ?>" required>
                                <?php if (isset($data['errors']['email'])): ?>
                                    <div class="text-danger small"><?= $data['errors']['email'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['phone'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Web</label>
                                <input type="url" name="website" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['website'] ?? '') ?>"
                                       placeholder="https://...">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pays</label>
                                <input type="text" name="country" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['country'] ?? 'Tunisia') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ville</label>
                                <input type="text" name="city" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['city'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code Postal</label>
                                <input type="text" name="postal_code" class="form-control"
                                       value="<?= htmlspecialchars($data['company']['postal_code'] ?? '') ?>">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Adresse</label>
                                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($data['company']['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Statut</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Statut de l'entreprise</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $data['company']['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="suspended" <?= $data['company']['status'] === 'suspended' ? 'selected' : '' ?>>Suspendue</option>
                                <option value="inactive" <?= $data['company']['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Créée le</label>
                            <input type="text" class="form-control"
                                   value="<?= date('d/m/Y H:i', strtotime($data['company']['created_at'])) ?>"
                                   readonly>
                        </div>

                        <?php if (isset($data['company']['updated_at'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Dernière mise à jour</label>
                            <input type="text" class="form-control"
                                   value="<?= date('d/m/Y H:i', strtotime($data['company']['updated_at'])) ?>"
                                   readonly>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Abonnement -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Abonnement</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Plan d'abonnement</label>
                            <select name="subscription_plan" class="form-select">
                                <option value="starter" <?= ($data['company']['subscription_plan'] ?? '') === 'starter' ? 'selected' : '' ?>>Starter</option>
                                <option value="professional" <?= ($data['company']['subscription_plan'] ?? '') === 'professional' ? 'selected' : '' ?>>Professional</option>
                                <option value="enterprise" <?= ($data['company']['subscription_plan'] ?? '') === 'enterprise' ? 'selected' : '' ?>>Enterprise</option>
                                <option value="custom" <?= ($data['company']['subscription_plan'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Statut abonnement</label>
                            <select name="subscription_status" class="form-select">
                                <option value="trial" <?= ($data['company']['subscription_status'] ?? '') === 'trial' ? 'selected' : '' ?>>Trial</option>
                                <option value="active" <?= ($data['company']['subscription_status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="past_due" <?= ($data['company']['subscription_status'] ?? '') === 'past_due' ? 'selected' : '' ?>>Past Due</option>
                                <option value="cancelled" <?= ($data['company']['subscription_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="expired" <?= ($data['company']['subscription_status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Devise</label>
                            <select name="currency" class="form-select">
                                <option value="TND" <?= ($data['company']['currency'] ?? 'TND') === 'TND' ? 'selected' : '' ?>>TND - Dinar Tunisien</option>
                                <option value="EUR" <?= ($data['company']['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                <option value="USD" <?= ($data['company']['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD - Dollar</option>
                            </select>
                        </div>

                        <?php if (isset($data['company']['trial_ends_at'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Fin de la période d'essai</label>
                            <input type="date" name="trial_ends_at" class="form-control"
                                   value="<?= date('Y-m-d', strtotime($data['company']['trial_ends_at'])) ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Limites de ressources -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Limites de Ressources</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Max Utilisateurs</label>
                            <input type="number" name="max_users" class="form-control"
                                   value="<?= $data['company']['max_users'] ?? 5 ?>" min="1">
                            <?php if (isset($data['stats'])): ?>
                                <small class="text-muted">Actuellement: <?= $data['stats']['total_users'] ?? 0 ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Véhicules</label>
                            <input type="number" name="max_vehicles" class="form-control"
                                   value="<?= $data['company']['max_vehicles'] ?? 10 ?>" min="1">
                            <?php if (isset($data['stats'])): ?>
                                <small class="text-muted">Actuellement: <?= $data['stats']['total_vehicles'] ?? 0 ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Chauffeurs</label>
                            <input type="number" name="max_drivers" class="form-control"
                                   value="<?= $data['company']['max_drivers'] ?? 10 ?>" min="1">
                            <?php if (isset($data['stats'])): ?>
                                <small class="text-muted">Actuellement: <?= $data['stats']['total_drivers'] ?? 0 ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Branding -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Personnalisation</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($data['company']['logo'])): ?>
                        <div class="mb-3 text-center">
                            <label class="form-label">Logo actuel</label>
                            <div>
                                <img src="<?= htmlspecialchars($data['company']['logo']) ?>"
                                     alt="Logo" style="max-width: 150px; max-height: 80px;">
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nouveau Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="text-muted">PNG, JPG, max 2MB. Laissez vide pour conserver l'actuel</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Couleur Primaire</label>
                            <input type="color" name="primary_color" class="form-control form-control-color"
                                   value="<?= htmlspecialchars($data['company']['primary_color'] ?? '#007bff') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select">
                                <option value="Africa/Tunis" <?= ($data['company']['timezone'] ?? 'Africa/Tunis') === 'Africa/Tunis' ? 'selected' : '' ?>>Africa/Tunis (UTC+1)</option>
                                <option value="Europe/Paris" <?= ($data['company']['timezone'] ?? '') === 'Europe/Paris' ? 'selected' : '' ?>>Europe/Paris (UTC+1/+2)</option>
                                <option value="UTC" <?= ($data['company']['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Langue</label>
                            <select name="language" class="form-select">
                                <option value="fr" <?= ($data['company']['language'] ?? 'fr') === 'fr' ? 'selected' : '' ?>>Français</option>
                                <option value="ar" <?= ($data['company']['language'] ?? '') === 'ar' ? 'selected' : '' ?>>العربية</option>
                                <option value="en" <?= ($data['company']['language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= APP_URL ?>/companies/view/<?= $data['company']['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="card border-danger mt-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Zone Dangereuse</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6>Supprimer cette entreprise</h6>
                    <p class="text-muted mb-0">
                        Une fois supprimée, toutes les données associées (utilisateurs, véhicules, etc.) seront définitivement perdues.
                        Cette action est irréversible.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i> Supprimer l'entreprise
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirmer la Suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Attention !</strong> Vous êtes sur le point de supprimer l'entreprise <strong><?= htmlspecialchars($data['company']['company_name']) ?></strong>.</p>
                <p>Cette action supprimera définitivement :</p>
                <ul>
                    <li>Tous les utilisateurs de l'entreprise</li>
                    <li>Tous les véhicules et leur historique</li>
                    <li>Tous les chauffeurs</li>
                    <li>Toutes les données de tracking</li>
                    <li>Tous les rapports et statistiques</li>
                </ul>
                <p class="text-danger"><strong>Cette action est irréversible !</strong></p>
                <div class="mb-3">
                    <label class="form-label">Tapez <strong><?= htmlspecialchars($data['company']['company_code']) ?></strong> pour confirmer :</label>
                    <input type="text" id="deleteConfirmation" class="form-control" placeholder="<?= htmlspecialchars($data['company']['company_code']) ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= APP_URL ?>/companies/delete/<?= $data['company']['id'] ?>" style="display: inline;">
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                        <i class="fas fa-trash"></i> Supprimer Définitivement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('deleteConfirmation').addEventListener('input', function() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const expectedCode = '<?= htmlspecialchars($data['company']['company_code']) ?>';

    if (this.value === expectedCode) {
        confirmBtn.disabled = false;
    } else {
        confirmBtn.disabled = true;
    }
});
</script>

<?php require APP_PATH . '/views/includes/footer.php'; ?>
