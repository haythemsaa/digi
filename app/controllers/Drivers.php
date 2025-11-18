<?php
/**
 * Drivers & HR Controller
 */

class Drivers extends Controller {

    private $driversModel;
    private $userModel;
    private $vehicleModel;

    public function __construct() {
        $this->driversModel = $this->model('Drivers');
        $this->userModel = $this->model('User');
        $this->vehicleModel = $this->model('Vehicle');
    }

    public function index() {
        $drivers = $this->driversModel->getAllDriverProfiles();
        $expiringLicenses = $this->driversModel->getExpiringLicenses(30);

        $data = [
            'drivers' => $drivers,
            'expiring_licenses' => $expiringLicenses,
            'active_menu' => 'drivers',
            'page_title' => 'Drivers & HR Management'
        ];

        $this->view('drivers/index', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->driversModel->addDriverProfile($_POST)) {
                $_SESSION['success'] = 'Driver added successfully';
                $this->redirect('drivers');
            } else {
                $_SESSION['error'] = 'Failed to add driver';
            }
        }

        $data = [
            'active_menu' => 'drivers',
            'page_title' => 'Add Driver'
        ];

        $this->view('drivers/add', $data);
    }

    public function edit($id) {
        $driver = $this->driversModel->getDriverProfileById($id);

        if (!$driver) {
            $_SESSION['error'] = 'Driver not found';
            $this->redirect('drivers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->driversModel->updateDriverProfile($id, $_POST)) {
                $_SESSION['success'] = 'Driver updated successfully';
                $this->redirect('drivers');
            } else {
                $_SESSION['error'] = 'Failed to update driver';
            }
        }

        $data = [
            'driver' => $driver,
            'active_menu' => 'drivers',
            'page_title' => 'Edit Driver'
        ];

        $this->view('drivers/edit', $data);
    }

    public function infractions() {
        $infractions = $this->driversModel->getDriverInfractions();

        $data = [
            'infractions' => $infractions,
            'active_menu' => 'drivers',
            'page_title' => 'Driver Infractions'
        ];

        $this->view('drivers/infractions', $data);
    }

    public function addInfraction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->driversModel->addInfraction($_POST)) {
                $_SESSION['success'] = 'Infraction added successfully';
                $this->redirect('drivers/infractions');
            } else {
                $_SESSION['error'] = 'Failed to add infraction';
            }
        }

        $drivers = $this->userModel->getDrivers();
        $vehicles = $this->vehicleModel->getAllVehicles();

        $data = [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'active_menu' => 'drivers',
            'page_title' => 'Add Infraction'
        ];

        $this->view('drivers/add_infraction', $data);
    }
}
