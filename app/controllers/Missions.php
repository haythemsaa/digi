<?php

class Missions extends Controller {
    private $missionModel;
    private $vehicleModel;

    public function __construct() {
        // Check module access
        requireModule('missions');

        $this->missionModel = $this->model('Mission');
        $this->vehicleModel = $this->model('Vehicle');
    }

    /**
     * Dashboard - Missions overview
     */
    public function index() {
        $data = [
            'page_title' => 'Gestion des Missions',
            'active_menu' => 'missions',
            'stats' => $this->missionModel->getDashboardStats(),
            'upcoming_missions' => $this->missionModel->getUpcomingMissions(7),
            'recent_missions' => $this->missionModel->getAllMissions(null, 10)
        ];

        $this->view('missions/index', $data);
    }

    /**
     * List all missions
     */
    public function all() {
        $status = $_GET['status'] ?? null;

        $data = [
            'page_title' => 'Toutes les Missions',
            'active_menu' => 'missions',
            'missions' => $this->missionModel->getAllMissions($status, 200),
            'filter_status' => $status
        ];

        $this->view('missions/all', $data);
    }

    /**
     * Create new mission
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $missionData = [
                'mission_type' => $_POST['mission_type'],
                'client_name' => $_POST['client_name'],
                'client_phone' => $_POST['client_phone'] ?? null,
                'client_email' => $_POST['client_email'] ?? null,
                'client_address' => $_POST['client_address'] ?? null,
                'description' => $_POST['description'] ?? null,
                'priority' => $_POST['priority'] ?? 'normal',
                'scheduled_start' => $_POST['scheduled_start'] ?? null,
                'scheduled_end' => $_POST['scheduled_end'] ?? null,
                'assigned_vehicle_id' => $_POST['assigned_vehicle_id'] ?? null,
                'assigned_driver_id' => $_POST['assigned_driver_id'] ?? null,
                'pickup_location' => $_POST['pickup_location'] ?? null,
                'delivery_location' => $_POST['delivery_location'] ?? null,
                'estimated_distance_km' => $_POST['estimated_distance_km'] ?? null,
                'estimated_duration_minutes' => $_POST['estimated_duration_minutes'] ?? null,
                'notes' => $_POST['notes'] ?? null
            ];

            $missionId = $this->missionModel->createMission($missionData);

            if ($missionId) {
                // Add items if provided
                if (!empty($_POST['items'])) {
                    foreach ($_POST['items'] as $item) {
                        if (!empty($item['item_name'])) {
                            $itemData = [
                                'mission_id' => $missionId,
                                'item_name' => $item['item_name'],
                                'item_description' => $item['item_description'] ?? null,
                                'quantity' => $item['quantity'] ?? 1,
                                'weight_kg' => $item['weight_kg'] ?? null,
                                'fragile' => isset($item['fragile']) ? 1 : 0
                            ];
                            $this->missionModel->addMissionItem($itemData);
                        }
                    }
                }

                flash('success', 'Mission créée avec succès');
                redirect('missions/view/' . $missionId);
            } else {
                flash('error', 'Erreur lors de la création');
            }
        }

        $data = [
            'page_title' => 'Nouvelle Mission',
            'active_menu' => 'missions',
            'vehicles' => $this->vehicleModel->getAllVehicles()
        ];

        $this->view('missions/create', $data);
    }

    /**
     * View mission details
     */
    public function view($id) {
        $mission = $this->missionModel->getMissionById($id);

        if (!$mission) {
            flash('error', 'Mission non trouvée');
            redirect('missions');
        }

        $data = [
            'page_title' => 'Mission ' . $mission['mission_number'],
            'active_menu' => 'missions',
            'mission' => $mission,
            'items' => $this->missionModel->getMissionItems($id),
            'billing' => $this->missionModel->getMissionBilling($id),
            'updates' => $this->missionModel->getMissionUpdates($id)
        ];

        $this->view('missions/view', $data);
    }

    /**
     * Update mission status
     */
    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newStatus = $_POST['status'];

            if ($this->missionModel->updateStatus($id, $newStatus)) {
                flash('success', 'Statut mis à jour');
            } else {
                flash('error', 'Erreur lors de la mise à jour');
            }
        }

        redirect('missions/view/' . $id);
    }

    /**
     * Edit mission
     */
    public function edit($id) {
        $mission = $this->missionModel->getMissionById($id);

        if (!$mission) {
            flash('error', 'Mission non trouvée');
            redirect('missions');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $missionData = [
                'mission_type' => $_POST['mission_type'],
                'client_name' => $_POST['client_name'],
                'client_phone' => $_POST['client_phone'] ?? null,
                'client_email' => $_POST['client_email'] ?? null,
                'description' => $_POST['description'] ?? null,
                'priority' => $_POST['priority'],
                'scheduled_start' => $_POST['scheduled_start'] ?? null,
                'scheduled_end' => $_POST['scheduled_end'] ?? null,
                'assigned_vehicle_id' => $_POST['assigned_vehicle_id'] ?? null,
                'assigned_driver_id' => $_POST['assigned_driver_id'] ?? null,
                'pickup_location' => $_POST['pickup_location'] ?? null,
                'delivery_location' => $_POST['delivery_location'] ?? null,
                'estimated_distance_km' => $_POST['estimated_distance_km'] ?? null,
                'notes' => $_POST['notes'] ?? null
            ];

            if ($this->missionModel->updateMission($id, $missionData)) {
                flash('success', 'Mission mise à jour');
                redirect('missions/view/' . $id);
            } else {
                flash('error', 'Erreur lors de la mise à jour');
            }
        }

        $data = [
            'page_title' => 'Modifier Mission',
            'active_menu' => 'missions',
            'mission' => $mission,
            'vehicles' => $this->vehicleModel->getAllVehicles()
        ];

        $this->view('missions/edit', $data);
    }

    /**
     * Mission calendar view
     */
    public function calendar() {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        $startDate = $year . '-' . $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $data = [
            'page_title' => 'Planning des Missions',
            'active_menu' => 'missions',
            'missions' => $this->missionModel->getMissionsByDateRange($startDate, $endDate),
            'month' => $month,
            'year' => $year
        ];

        $this->view('missions/calendar', $data);
    }

    /**
     * Create billing/invoice for mission
     */
    public function createBilling($missionId) {
        $mission = $this->missionModel->getMissionById($missionId);

        if (!$mission) {
            flash('error', 'Mission non trouvée');
            redirect('missions');
        }

        // Check if billing already exists
        $existingBilling = $this->missionModel->getMissionBilling($missionId);
        if ($existingBilling) {
            flash('error', 'Une facture existe déjà pour cette mission');
            redirect('missions/view/' . $missionId);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $billingData = [
                'mission_id' => $missionId,
                'base_rate' => $_POST['base_rate'],
                'distance_charge' => $_POST['distance_charge'] ?? 0,
                'time_charge' => $_POST['time_charge'] ?? 0,
                'additional_charges' => $_POST['additional_charges'] ?? 0,
                'tax_rate' => $_POST['tax_rate'] ?? 20,
                'discount_amount' => $_POST['discount_amount'] ?? 0
            ];

            if ($this->missionModel->createBilling($billingData)) {
                flash('success', 'Facture créée avec succès');
                redirect('missions/view/' . $missionId);
            } else {
                flash('error', 'Erreur lors de la création de la facture');
            }
        }

        $data = [
            'page_title' => 'Facturation Mission',
            'active_menu' => 'missions',
            'mission' => $mission
        ];

        $this->view('missions/create_billing', $data);
    }

    /**
     * Mark billing as paid
     */
    public function markPaid($billingId) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $paymentMethod = $_POST['payment_method'];
            $paymentReference = $_POST['payment_reference'] ?? null;

            if ($this->missionModel->updateBillingPayment($billingId, $paymentMethod, $paymentReference)) {
                flash('success', 'Paiement enregistré');
            } else {
                flash('error', 'Erreur lors de l\'enregistrement');
            }
        }

        redirect($_SERVER['HTTP_REFERER'] ?? 'missions');
    }

    /**
     * Analytics/Reports
     */
    public function analytics() {
        $data = [
            'page_title' => 'Rapports & Statistiques',
            'active_menu' => 'missions',
            'stats' => $this->missionModel->getDashboardStats()
        ];

        $this->view('missions/analytics', $data);
    }
}
