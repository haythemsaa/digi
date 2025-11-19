<?php require_once '../app/views/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-plus"></i> Nouvelle Réservation</h1>
            <p class="text-muted">Créer une réservation taxi ou bus</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/bookings" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux Réservations
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/passenger_transport/createBooking">
        <div class="row">
            <!-- Left Column: Booking Details -->
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0"><i class="fas fa-map-marked-alt"></i> Détails de la Course</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="booking_type">Type de Service <span class="text-danger">*</span></label>
                                    <select class="form-control" id="booking_type" name="booking_type" required>
                                        <option value="taxi">Taxi</option>
                                        <option value="bus">Bus</option>
                                        <option value="shuttle">Navette</option>
                                        <option value="corporate">Corporate</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ride_type">Type de Course <span class="text-danger">*</span></label>
                                    <select class="form-control" id="ride_type" name="ride_type" required>
                                        <option value="immediate">Immédiate</option>
                                        <option value="scheduled">Programmée</option>
                                        <option value="recurring">Récurrente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="scheduled_time_field" style="display: none;">
                            <div class="form-group">
                                <label for="scheduled_pickup_time">Heure de Prise en Charge Programmée</label>
                                <input type="datetime-local" class="form-control" id="scheduled_pickup_time" name="scheduled_pickup_time">
                            </div>
                        </div>

                        <hr>
                        <h6><i class="fas fa-map-marker-alt text-success"></i> Point de Départ</h6>

                        <div class="form-group">
                            <label for="pickup_address">Adresse de Prise en Charge <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="pickup_address" name="pickup_address" rows="2" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup_latitude">Latitude</label>
                                    <input type="number" step="0.000001" class="form-control" id="pickup_latitude" name="pickup_latitude" placeholder="36.8065">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup_longitude">Longitude</label>
                                    <input type="number" step="0.000001" class="form-control" id="pickup_longitude" name="pickup_longitude" placeholder="10.1815">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6><i class="fas fa-map-marker-alt text-danger"></i> Point d'Arrivée</h6>

                        <div class="form-group">
                            <label for="dropoff_address">Adresse de Destination <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="dropoff_address" name="dropoff_address" rows="2" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dropoff_latitude">Latitude</label>
                                    <input type="number" step="0.000001" class="form-control" id="dropoff_latitude" name="dropoff_latitude">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dropoff_longitude">Longitude</label>
                                    <input type="number" step="0.000001" class="form-control" id="dropoff_longitude" name="dropoff_longitude">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6><i class="fas fa-info-circle"></i> Informations Complémentaires</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="passenger_count">Nombre de Passagers</label>
                                    <input type="number" class="form-control" id="passenger_count" name="passenger_count" min="1" max="50" value="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="comfort_class">Classe de Confort</label>
                                    <select class="form-control" id="comfort_class" name="comfort_class">
                                        <option value="economy">Économique</option>
                                        <option value="standard" selected>Standard</option>
                                        <option value="comfort">Confort</option>
                                        <option value="premium">Premium</option>
                                        <option value="luxury">Luxe</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estimated_distance_km">Distance Estimée (km)</label>
                                    <input type="number" step="0.1" class="form-control" id="estimated_distance_km" name="estimated_distance_km" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estimated_duration_minutes">Durée Estimée (minutes)</label>
                                    <input type="number" class="form-control" id="estimated_duration_minutes" name="estimated_duration_minutes" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="special_requirements">Exigences Spéciales</label>
                            <textarea class="form-control" id="special_requirements" name="special_requirements" rows="2" placeholder="Fauteuil roulant, siège bébé, etc."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Mode de Paiement</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="cash" selected>Espèces</option>
                                <option value="card">Carte Bancaire</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="account">Compte Client</option>
                                <option value="voucher">Bon/Voucher</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes / Instructions</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Passenger Selection -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-user"></i> Informations Passager</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Sélectionner un passager existant</label>
                            <select class="form-control" id="passenger_id" name="passenger_id">
                                <option value="">-- Nouveau passager --</option>
                                <?php foreach ($data['passengers'] as $passenger): ?>
                                    <option value="<?php echo $passenger['id']; ?>"
                                            data-phone="<?php echo $passenger['phone']; ?>"
                                            data-email="<?php echo $passenger['email']; ?>">
                                        <?php echo htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']); ?>
                                        (<?php echo $passenger['phone']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="new_passenger_fields">
                            <hr>
                            <h6>Nouveau Passager</h6>

                            <div class="form-group">
                                <label for="passenger_first_name">Prénom</label>
                                <input type="text" class="form-control" id="passenger_first_name" name="passenger_first_name">
                            </div>

                            <div class="form-group">
                                <label for="passenger_last_name">Nom</label>
                                <input type="text" class="form-control" id="passenger_last_name" name="passenger_last_name">
                            </div>

                            <div class="form-group">
                                <label for="passenger_phone">Téléphone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="passenger_phone" name="passenger_phone" required>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                Si le numéro de téléphone existe déjà dans le système, le passager sera automatiquement associé.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Estimated Fare Display -->
                <div class="card shadow border-left-warning">
                    <div class="card-body">
                        <h6 class="text-warning"><i class="fas fa-calculator"></i> Tarif Estimé</h6>
                        <h3 class="mb-0" id="estimated_fare_display">
                            <span class="text-gray-800">-- TND</span>
                        </h3>
                        <small class="text-muted">Le tarif final peut varier</small>
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
                            <i class="fas fa-save"></i> Créer la Réservation
                        </button>
                        <a href="<?php echo APP_URL; ?>/passenger_transport/bookings" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Show/hide scheduled time field
document.getElementById('ride_type').addEventListener('change', function() {
    const scheduledField = document.getElementById('scheduled_time_field');
    if (this.value === 'scheduled' || this.value === 'recurring') {
        scheduledField.style.display = 'block';
        document.getElementById('scheduled_pickup_time').required = true;
    } else {
        scheduledField.style.display = 'none';
        document.getElementById('scheduled_pickup_time').required = false;
    }
});

// Show/hide new passenger fields
document.getElementById('passenger_id').addEventListener('change', function() {
    const newPassengerFields = document.getElementById('new_passenger_fields');
    if (this.value === '') {
        newPassengerFields.style.display = 'block';
    } else {
        newPassengerFields.style.display = 'none';
    }
});

// Simple fare estimation
function calculateEstimatedFare() {
    const distance = parseFloat(document.getElementById('estimated_distance_km').value) || 0;
    const duration = parseFloat(document.getElementById('estimated_duration_minutes').value) || 0;
    const comfortClass = document.getElementById('comfort_class').value;

    const baseFare = 3.00;
    const pricePerKm = 0.80;
    const pricePerMinute = 0.10;

    let fare = baseFare + (distance * pricePerKm) + (duration * pricePerMinute);

    // Comfort multipliers
    const multipliers = {
        'economy': 0.8,
        'standard': 1.0,
        'comfort': 1.3,
        'premium': 1.6,
        'luxury': 2.0
    };

    fare *= (multipliers[comfortClass] || 1.0);

    document.getElementById('estimated_fare_display').innerHTML =
        '<span class="text-success">' + fare.toFixed(2) + ' TND</span>';
}

// Recalculate on change
document.getElementById('estimated_distance_km').addEventListener('input', calculateEstimatedFare);
document.getElementById('estimated_duration_minutes').addEventListener('input', calculateEstimatedFare);
document.getElementById('comfort_class').addEventListener('change', calculateEstimatedFare);

// Initial calculation
calculateEstimatedFare();
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
