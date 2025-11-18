<?php
/**
 * Vehicles Controller - Fleet Management
 */

class Vehicles extends Controller {

    private $vehicleModel;
    private $userModel;

    public function __construct() {
        $this->vehicleModel = $this->model('Vehicle');
        $this->userModel = $this->model('User');
    }

    /**
     * List all vehicles
     */
    public function index() {
        $vehicles = $this->vehicleModel->getAllVehicles();

        $data = [
            'vehicles' => $vehicles,
            'active_menu' => 'vehicles',
            'page_title' => 'Fleet Management'
        ];

        $this->view('vehicles/index', $data);
    }

    /**
     * View vehicle details
     */
    public function view($id) {
        $vehicle = $this->vehicleModel->getVehicleById($id);

        if (!$vehicle) {
            $_SESSION['error'] = 'Vehicle not found';
            $this->redirect('vehicles');
        }

        $data = [
            'vehicle' => $vehicle,
            'active_menu' => 'vehicles',
            'page_title' => 'Vehicle Details - ' . $vehicle['registration_number']
        ];

        $this->view('vehicles/view', $data);
    }

    /**
     * Add new vehicle
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = $_POST;

            // Validate required fields
            $errors = $this->validateInput($data, [
                'registration_number' => 'required',
                'brand' => 'required',
                'model' => 'required',
                'type' => 'required',
                'fuel_type' => 'required'
            ]);

            if (empty($errors)) {
                // Handle file upload if there's a photo
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = PUBLIC_PATH . '/uploads/vehicles/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
                    $uploadFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                        $data['photo'] = 'uploads/vehicles/' . $fileName;
                    }
                }

                if ($this->vehicleModel->addVehicle($data)) {
                    $_SESSION['success'] = 'Vehicle added successfully';
                    $this->redirect('vehicles');
                } else {
                    $_SESSION['error'] = 'Failed to add vehicle';
                }
            }

            $viewData = array_merge($data, $errors);
        } else {
            $viewData = [
                'registration_number' => '',
                'vin' => '',
                'brand' => '',
                'model' => '',
                'year' => '',
                'color' => '',
                'type' => '',
                'fuel_type' => '',
                'status' => 'active'
            ];
        }

        $viewData['active_menu'] = 'vehicles';
        $viewData['page_title'] = 'Add New Vehicle';

        $this->view('vehicles/add', $viewData);
    }

    /**
     * Edit vehicle
     */
    public function edit($id) {
        $vehicle = $this->vehicleModel->getVehicleById($id);

        if (!$vehicle) {
            $_SESSION['error'] = 'Vehicle not found';
            $this->redirect('vehicles');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = $_POST;

            // Validate required fields
            $errors = $this->validateInput($data, [
                'registration_number' => 'required',
                'brand' => 'required',
                'model' => 'required',
                'type' => 'required',
                'fuel_type' => 'required'
            ]);

            if (empty($errors)) {
                // Handle file upload if there's a new photo
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = PUBLIC_PATH . '/uploads/vehicles/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
                    $uploadFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                        $data['photo'] = 'uploads/vehicles/' . $fileName;
                    }
                } else {
                    $data['photo'] = $vehicle['photo'];
                }

                if ($this->vehicleModel->updateVehicle($id, $data)) {
                    $_SESSION['success'] = 'Vehicle updated successfully';
                    $this->redirect('vehicles/view/' . $id);
                } else {
                    $_SESSION['error'] = 'Failed to update vehicle';
                }
            }

            $viewData = array_merge($data, $errors);
        } else {
            $viewData = $vehicle;
        }

        $viewData['active_menu'] = 'vehicles';
        $viewData['page_title'] = 'Edit Vehicle - ' . $vehicle['registration_number'];

        $this->view('vehicles/edit', $viewData);
    }

    /**
     * Delete vehicle
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->vehicleModel->deleteVehicle($id)) {
                $_SESSION['success'] = 'Vehicle deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete vehicle';
            }
        }

        $this->redirect('vehicles');
    }

    /**
     * Search vehicles (AJAX)
     */
    public function search() {
        if (!isset($_GET['q'])) {
            $this->jsonResponse(['error' => 'No search query'], 400);
        }

        $results = $this->vehicleModel->searchVehicles($_GET['q']);
        $this->jsonResponse(['results' => $results]);
    }
}
