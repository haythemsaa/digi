<?php require APP_PATH . '/views/includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-building"></i> Créer une Entreprise</h2>
        <a href="<?= APP_URL ?>/companies" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php flash('error'); ?>

    <form method="POST" action="<?= APP_URL ?>/companies/create" enctype="multipart/form-data">
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
                                       value="<?= $data['company_name'] ?? '' ?>" required>
                                <?php if (isset($data['errors']['company_name'])): ?>
                                    <div class="text-danger small"><?= $data['errors']['company_name'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code Entreprise</label>
                                <input type="text" name="company_code" class="form-control"
                                       value="<?= $data['company_code'] ?? '' ?>"
                                       placeholder="Laissez vide pour génération auto">
                                <small class="text-muted">Ex: DEMO2025001</small>
                                <?php if (isset($data['errors']['company_code'])): ?>
                                    <div class="text-danger small"><?= $data['errors']['company_code'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Raison Sociale</label>
                                <input type="text" name="legal_name" class="form-control"
                                       value="<?= $data['legal_name'] ?? '' ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type d'entreprise</label>
                                <select name="business_type" class="form-select">
                                    <option value="">Sélectionner...</option>
                                    <option value="sarl">SARL</option>
                                    <option value="sa">SA</option>
                                    <option value="suarl">SUARL</option>
                                    <option value="individual">Individuelle</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Matricule Fiscal</label>
                                <input type="text" name="tax_id" class="form-control"
                                       value="<?= $data['tax_id'] ?? '' ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registre Commerce</label>
                                <input type="text" name="registration_number" class="form-control"
                                       value="<?= $data['registration_number'] ?? '' ?>">
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
                                       value="<?= $data['email'] ?? '' ?>" required>
                                <?php if (isset($data['errors']['email'])): ?>
                                    <div class="text-danger small"><?= $data['errors']['email'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="<?= $data['phone'] ?? '' ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Web</label>
                                <input type="url" name="website" class="form-control"
                                       value="<?= $data['website'] ?? '' ?>"
                                       placeholder="https://...">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pays</label>
                                <input type="text" name="country" class="form-control"
                                       value="<?= $data['country'] ?? 'Tunisia' ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ville</label>
                                <input type="text" name="city" class="form-control"
                                       value="<?= $data['city'] ?? '' ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code Postal</label>
                                <input type="text" name="postal_code" class="form-control"
                                       value="<?= $data['postal_code'] ?? '' ?>">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Adresse</label>
                                <textarea name="address" class="form-control" rows="2"><?= $data['address'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Abonnement -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Abonnement</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Plan d'abonnement</label>
                            <select name="subscription_plan" class="form-select">
                                <option value="starter">Starter</option>
                                <option value="professional">Professional</option>
                                <option value="enterprise">Enterprise</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Période d'essai (jours)</label>
                            <input type="number" name="trial_days" class="form-control"
                                   value="<?= $data['trial_days'] ?? 30 ?>" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Devise</label>
                            <select name="currency" class="form-select">
                                <option value="TND">TND - Dinar Tunisien</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="USD">USD - Dollar</option>
                            </select>
                        </div>
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
                                   value="<?= $data['max_users'] ?? 5 ?>" min="1">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Véhicules</label>
                            <input type="number" name="max_vehicles" class="form-control"
                                   value="<?= $data['max_vehicles'] ?? 10 ?>" min="1">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Chauffeurs</label>
                            <input type="number" name="max_drivers" class="form-control"
                                   value="<?= $data['max_drivers'] ?? 10 ?>" min="1">
                        </div>
                    </div>
                </div>

                <!-- Branding -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Personnalisation</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="text-muted">PNG, JPG, max 2MB</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Couleur Primaire</label>
                            <input type="color" name="primary_color" class="form-control form-control-color"
                                   value="<?= $data['primary_color'] ?? '#007bff' ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select">
                                <option value="Africa/Tunis">Africa/Tunis (UTC+1)</option>
                                <option value="Europe/Paris">Europe/Paris (UTC+1/+2)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Langue</label>
                            <select name="language" class="form-select">
                                <option value="fr">Français</option>
                                <option value="ar">العربية</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="<?= APP_URL ?>/companies" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer l'entreprise
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require APP_PATH . '/views/includes/footer.php'; ?>
