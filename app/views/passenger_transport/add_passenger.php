<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-user-plus"></i> Ajouter un Passager</h1>
            <p class="text-muted">Enregistrer un nouveau client/passager</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/passengers" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux Passagers
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/passenger_transport/addPassenger">
        <div class="row">
            <!-- Left Column: Personal Info -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0"><i class="fas fa-user"></i> Informations Personnelles</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" required
                                   placeholder="+216 XX XXX XXX">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="exemple@email.com">
                        </div>

                        <div class="form-group">
                            <label for="id_card_number">Numéro CIN</label>
                            <input type="text" class="form-control" id="id_card_number" name="id_card_number">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth">Date de Naissance</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">Genre</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="">-- Sélectionner --</option>
                                        <option value="male">Homme</option>
                                        <option value="female">Femme</option>
                                        <option value="other">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Address & Preferences -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-map-marker-alt"></i> Adresse</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="address">Adresse Complète</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="city">Ville</label>
                                    <input type="text" class="form-control" id="city" name="city" placeholder="Tunis">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code">Code Postal</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0"><i class="fas fa-cog"></i> Préférences</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="preferred_payment_method">Mode de Paiement Préféré</label>
                            <select class="form-control" id="preferred_payment_method" name="preferred_payment_method">
                                <option value="cash" selected>Espèces</option>
                                <option value="card">Carte Bancaire</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="account">Compte Client</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes / Commentaires</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Informations supplémentaires..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Enregistrer le Passager
                        </button>
                        <a href="<?php echo APP_URL; ?>/passenger_transport/passengers" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>
