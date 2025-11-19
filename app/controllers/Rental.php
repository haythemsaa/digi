<?php
/**
 * Rental Controller
 * Manages vehicle rental/location operations
 */

class Rental extends Controller {
    private $rentalModel;
    private $vehicleModel;
    private $clientModel;

    public function __construct() {
        $this->rentalModel = $this->model('Rental');
        $this->vehicleModel = $this->model('Vehicle');
        $this->clientModel = $this->model('Client');
    }

    /**
     * Index - List all rental contracts
     */
    public function index() {
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['payment_status'])) {
            $filters['payment_status'] = $_GET['payment_status'];
        }

        $contracts = $this->rentalModel->getAllContracts($filters);

        $data = [
            'page_title' => 'Rental Contracts',
            'contracts' => $contracts
        ];

        $this->view('rental/index', $data);
    }

    /**
     * Add new rental contract
     */
    public function addContract() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate data
            $errors = [];

            if (empty($_POST['client_id'])) {
                $errors[] = 'Client is required';
            }

            if (empty($_POST['vehicle_id'])) {
                $errors[] = 'Vehicle is required';
            }

            if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
                $errors[] = 'Start and end dates are required';
            }

            // Check vehicle availability
            if (!empty($_POST['vehicle_id']) && !empty($_POST['start_date']) && !empty($_POST['end_date'])) {
                if (!$this->rentalModel->checkVehicleAvailability($_POST['vehicle_id'], $_POST['start_date'], $_POST['end_date'])) {
                    $errors[] = 'Vehicle is not available for the selected dates';
                }
            }

            if (empty($errors)) {
                $contractId = $this->rentalModel->createContract($_POST);

                if ($contractId) {
                    $_SESSION['success'] = 'Rental contract created successfully';
                    $this->redirect('rental/view/' . $contractId);
                } else {
                    $_SESSION['error'] = 'Failed to create rental contract';
                }
            } else {
                $_SESSION['error'] = implode(', ', $errors);
            }
        }

        $data = [
            'page_title' => 'New Rental Contract',
            'clients' => $this->clientModel->getAllClients(),
            'vehicles' => $this->vehicleModel->getAllVehicles(['status' => 'available']),
            'rates' => $this->rentalModel->getAllRates()
        ];

        $this->view('rental/add_contract', $data);
    }

    /**
     * View rental contract details
     */
    public function view($id) {
        $contract = $this->rentalModel->getContractById($id);

        if (!$contract) {
            $_SESSION['error'] = 'Rental contract not found';
            $this->redirect('rental');
        }

        $inspections = $this->rentalModel->getContractInspections($id);
        $payments = $this->rentalModel->getContractPayments($id);

        $data = [
            'page_title' => 'Rental Contract Details',
            'contract' => $contract,
            'inspections' => $inspections,
            'payments' => $payments
        ];

        $this->view('rental/view', $data);
    }

    /**
     * Edit rental contract
     */
    public function edit($id) {
        $contract = $this->rentalModel->getContractById($id);

        if (!$contract) {
            $_SESSION['error'] = 'Rental contract not found';
            $this->redirect('rental');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check vehicle availability (excluding current contract)
            if (!empty($_POST['vehicle_id']) && !empty($_POST['start_date']) && !empty($_POST['end_date'])) {
                if (!$this->rentalModel->checkVehicleAvailability($_POST['vehicle_id'], $_POST['start_date'], $_POST['end_date'], $id)) {
                    $_SESSION['error'] = 'Vehicle is not available for the selected dates';
                } else {
                    if ($this->rentalModel->updateContract($id, $_POST)) {
                        $_SESSION['success'] = 'Rental contract updated successfully';
                        $this->redirect('rental/view/' . $id);
                    } else {
                        $_SESSION['error'] = 'Failed to update rental contract';
                    }
                }
            }
        }

        $data = [
            'page_title' => 'Edit Rental Contract',
            'contract' => $contract,
            'clients' => $this->clientModel->getAllClients(),
            'vehicles' => $this->vehicleModel->getAllVehicles(),
            'rates' => $this->rentalModel->getAllRates()
        ];

        $this->view('rental/edit', $data);
    }

    /**
     * Update contract status
     */
    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';

            if ($this->rentalModel->updateContractStatus($id, $status)) {
                $_SESSION['success'] = 'Contract status updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update contract status';
            }
        }

        $this->redirect('rental/view/' . $id);
    }

    /**
     * Add inspection
     */
    public function addInspection($contractId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST['contract_id'] = $contractId;

            if ($this->rentalModel->createInspection($_POST)) {
                $_SESSION['success'] = 'Inspection added successfully';

                // If pickup inspection, update contract status to active
                if ($_POST['inspection_type'] == 'pickup') {
                    $this->rentalModel->updateContractStatus($contractId, 'active');
                }

                // If return inspection, update contract status to completed
                if ($_POST['inspection_type'] == 'return') {
                    $this->rentalModel->updateContractStatus($contractId, 'completed');
                }
            } else {
                $_SESSION['error'] = 'Failed to add inspection';
            }
        }

        $this->redirect('rental/view/' . $contractId);
    }

    /**
     * Add payment
     */
    public function addPayment($contractId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST['contract_id'] = $contractId;

            if ($this->rentalModel->addPayment($_POST)) {
                $_SESSION['success'] = 'Payment added successfully';
            } else {
                $_SESSION['error'] = 'Failed to add payment';
            }
        }

        $this->redirect('rental/view/' . $contractId);
    }

    /**
     * Rental rates
     */
    public function rates() {
        $rates = $this->rentalModel->getAllRates();

        $data = [
            'page_title' => 'Rental Rates',
            'rates' => $rates
        ];

        $this->view('rental/rates', $data);
    }

    /**
     * Add rental rate
     */
    public function addRate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->rentalModel->createRate($_POST)) {
                $_SESSION['success'] = 'Rental rate created successfully';
                $this->redirect('rental/rates');
            } else {
                $_SESSION['error'] = 'Failed to create rental rate';
            }
        }

        $data = [
            'page_title' => 'New Rental Rate',
            'vehicles' => $this->vehicleModel->getAllVehicles()
        ];

        $this->view('rental/add_rate', $data);
    }

    /**
     * Edit rental rate
     */
    public function editRate($id) {
        $rate = $this->rentalModel->getRateById($id);

        if (!$rate) {
            $_SESSION['error'] = 'Rental rate not found';
            $this->redirect('rental/rates');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->rentalModel->updateRate($id, $_POST)) {
                $_SESSION['success'] = 'Rental rate updated successfully';
                $this->redirect('rental/rates');
            } else {
                $_SESSION['error'] = 'Failed to update rental rate';
            }
        }

        $data = [
            'page_title' => 'Edit Rental Rate',
            'rate' => $rate,
            'vehicles' => $this->vehicleModel->getAllVehicles()
        ];

        $this->view('rental/edit_rate', $data);
    }

    /**
     * Check vehicle availability (AJAX)
     */
    public function checkAvailability() {
        header('Content-Type: application/json');

        $vehicleId = $_GET['vehicle_id'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $excludeContractId = $_GET['exclude_contract_id'] ?? null;

        if ($vehicleId && $startDate && $endDate) {
            $available = $this->rentalModel->checkVehicleAvailability($vehicleId, $startDate, $endDate, $excludeContractId);
            echo json_encode(['available' => $available]);
        } else {
            echo json_encode(['available' => false, 'error' => 'Missing parameters']);
        }
        exit;
    }

    /**
     * Get rate by vehicle (AJAX)
     */
    public function getRateByVehicle() {
        header('Content-Type: application/json');

        $vehicleId = $_GET['vehicle_id'] ?? null;

        if ($vehicleId) {
            $rates = $this->rentalModel->getRatesByVehicle($vehicleId);
            echo json_encode(['success' => true, 'rates' => $rates]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing vehicle ID']);
        }
        exit;
    }

    /**
     * Delete contract
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->rentalModel->deleteContract($id)) {
                $_SESSION['success'] = 'Rental contract deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete rental contract';
            }
        }

        $this->redirect('rental');
    }
}
