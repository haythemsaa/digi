<?php

class Fuel extends Controller {
    private $fuelModel;
    private $vehicleModel;

    public function __construct() {
        // Check module access
        requireModule('fuel');

        $this->fuelModel = $this->model('Fuel');
        $this->vehicleModel = $this->model('Vehicle');
    }

    /**
     * Dashboard - Fuel overview
     */
    public function index() {
        $data = [
            'page_title' => 'Gestion du Carburant',
            'active_menu' => 'fuel',
            'stats' => $this->fuelModel->getDashboardStats(),
            'recent_transactions' => $this->fuelModel->getAllTransactions(10),
            'monthly_trends' => $this->fuelModel->getMonthlyTrends(6),
            'alerts' => $this->fuelModel->getActiveAlerts()
        ];

        $this->view('fuel/index', $data);
    }

    /**
     * Fuel transactions list
     */
    public function transactions() {
        $data = [
            'page_title' => 'Transactions Carburant',
            'active_menu' => 'fuel',
            'transactions' => $this->fuelModel->getAllTransactions(200)
        ];

        $this->view('fuel/transactions', $data);
    }

    /**
     * Add fuel transaction
     */
    public function addTransaction() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Calculate total amount
            $quantity = $_POST['quantity_liters'];
            $unitPrice = $_POST['unit_price'];
            $totalAmount = $quantity * $unitPrice;

            $transactionData = [
                'transaction_date' => $_POST['transaction_date'],
                'vehicle_id' => $_POST['vehicle_id'],
                'driver_id' => $_POST['driver_id'] ?? null,
                'fuel_card_id' => $_POST['fuel_card_id'] ?? null,
                'fuel_type' => $_POST['fuel_type'],
                'quantity_liters' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'odometer_reading' => $_POST['odometer_reading'] ?? null,
                'station_name' => $_POST['station_name'] ?? null,
                'payment_method' => $_POST['payment_method'] ?? 'fuel_card',
                'is_full_tank' => isset($_POST['is_full_tank']) ? 1 : 0,
                'notes' => $_POST['notes'] ?? null
            ];

            if ($this->fuelModel->createTransaction($transactionData)) {
                flash('success', 'Transaction enregistrée avec succès');
                redirect('fuel/transactions');
            } else {
                flash('error', 'Erreur lors de l\'enregistrement');
            }
        }

        $data = [
            'page_title' => 'Nouvelle Transaction',
            'active_menu' => 'fuel',
            'vehicles' => $this->vehicleModel->getAllVehicles(),
            'fuel_cards' => $this->fuelModel->getAllFuelCards()
        ];

        $this->view('fuel/add_transaction', $data);
    }

    /**
     * View transaction details
     */
    public function viewTransaction($id) {
        $transaction = $this->fuelModel->getTransactionById($id);

        if (!$transaction) {
            flash('error', 'Transaction non trouvée');
            redirect('fuel/transactions');
        }

        $data = [
            'page_title' => 'Détails Transaction',
            'active_menu' => 'fuel',
            'transaction' => $transaction
        ];

        $this->view('fuel/view_transaction', $data);
    }

    /**
     * Fuel cards management
     */
    public function cards() {
        $data = [
            'page_title' => 'Cartes Carburant',
            'active_menu' => 'fuel',
            'cards' => $this->fuelModel->getAllFuelCards()
        ];

        $this->view('fuel/cards', $data);
    }

    /**
     * Add fuel card
     */
    public function addCard() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cardData = [
                'card_number' => $_POST['card_number'],
                'card_type' => $_POST['card_type'] ?? 'physical',
                'provider' => $_POST['provider'] ?? null,
                'vehicle_id' => $_POST['vehicle_id'] ?? null,
                'driver_id' => $_POST['driver_id'] ?? null,
                'daily_limit' => $_POST['daily_limit'] ?? null,
                'monthly_limit' => $_POST['monthly_limit'] ?? null,
                'issue_date' => $_POST['issue_date'] ?? null,
                'expiry_date' => $_POST['expiry_date'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if ($this->fuelModel->createFuelCard($cardData)) {
                flash('success', 'Carte créée avec succès');
                redirect('fuel/cards');
            } else {
                flash('error', 'Erreur lors de la création');
            }
        }

        $data = [
            'page_title' => 'Nouvelle Carte',
            'active_menu' => 'fuel',
            'vehicles' => $this->vehicleModel->getAllVehicles()
        ];

        $this->view('fuel/add_card', $data);
    }

    /**
     * Consumption analytics
     */
    public function analytics() {
        $data = [
            'page_title' => 'Analyse Consommation',
            'active_menu' => 'fuel',
            'fleet_stats' => $this->fuelModel->getFleetConsumptionStats(),
            'monthly_trends' => $this->fuelModel->getMonthlyTrends(12)
        ];

        $this->view('fuel/analytics', $data);
    }

    /**
     * Vehicle consumption report
     */
    public function vehicleReport($vehicleId) {
        $vehicle = $this->vehicleModel->getVehicleById($vehicleId);

        if (!$vehicle) {
            flash('error', 'Véhicule non trouvé');
            redirect('fuel/analytics');
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $data = [
            'page_title' => 'Rapport Consommation',
            'active_menu' => 'fuel',
            'vehicle' => $vehicle,
            'consumption' => $this->fuelModel->getVehicleConsumption($vehicleId, $startDate, $endDate),
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $this->view('fuel/vehicle_report', $data);
    }

    /**
     * Fuel prices
     */
    public function prices() {
        $data = [
            'page_title' => 'Prix du Carburant',
            'active_menu' => 'fuel',
            'current_prices' => $this->fuelModel->getCurrentPrices()
        ];

        $this->view('fuel/prices', $data);
    }

    /**
     * Alerts
     */
    public function alerts() {
        $data = [
            'page_title' => 'Alertes Carburant',
            'active_menu' => 'fuel',
            'alerts' => $this->fuelModel->getActiveAlerts()
        ];

        $this->view('fuel/alerts', $data);
    }

    /**
     * Resolve alert
     */
    public function resolveAlert($alertId) {
        $userId = $_SESSION['user_id'] ?? null;

        if ($this->fuelModel->resolveAlert($alertId, $userId)) {
            flash('success', 'Alerte résolue');
        } else {
            flash('error', 'Erreur lors de la résolution');
        }

        redirect('fuel/alerts');
    }
}
