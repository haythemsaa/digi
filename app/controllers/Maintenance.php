<?php
/**
 * Maintenance Controller (GMAO)
 */

class Maintenance extends Controller {

    private $maintenanceModel;
    private $vehicleModel;
    private $userModel;

    public function __construct() {
        $this->maintenanceModel = $this->model('Maintenance');
        $this->vehicleModel = $this->model('Vehicle');
        $this->userModel = $this->model('User');
    }

    public function index() {
        $workOrders = $this->maintenanceModel->getAllWorkOrders();
        $dueMaintenances = $this->maintenanceModel->getDueMaintenances();

        $data = [
            'work_orders' => $workOrders,
            'due_maintenances' => $dueMaintenances,
            'active_menu' => 'maintenance',
            'page_title' => 'Maintenance Management (GMAO)'
        ];

        $this->view('maintenance/index', $data);
    }

    public function addWorkOrder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $_POST['reference'] = $this->maintenanceModel->generateWorkOrderReference();

            if ($this->maintenanceModel->addWorkOrder($_POST)) {
                $_SESSION['success'] = 'Work order created successfully';
                $this->redirect('maintenance');
            } else {
                $_SESSION['error'] = 'Failed to create work order';
            }
        }

        $vehicles = $this->vehicleModel->getAllVehicles();
        $mechanics = $this->userModel->getMechanics();
        $maintenanceTypes = $this->maintenanceModel->getAllMaintenanceTypes();

        $data = [
            'vehicles' => $vehicles,
            'mechanics' => $mechanics,
            'maintenance_types' => $maintenanceTypes,
            'active_menu' => 'maintenance',
            'page_title' => 'Create Work Order'
        ];

        $this->view('maintenance/add_work_order', $data);
    }

    public function viewWorkOrder($id) {
        $workOrder = $this->maintenanceModel->getWorkOrderById($id);

        if (!$workOrder) {
            $_SESSION['error'] = 'Work order not found';
            $this->redirect('maintenance');
        }

        $data = [
            'work_order' => $workOrder,
            'active_menu' => 'maintenance',
            'page_title' => 'Work Order ' . $workOrder['reference']
        ];

        $this->view('maintenance/view_work_order', $data);
    }

    public function schedules() {
        $schedules = $this->maintenanceModel->getMaintenanceSchedules();

        $data = [
            'schedules' => $schedules,
            'active_menu' => 'maintenance',
            'page_title' => 'Preventive Maintenance Schedules'
        ];

        $this->view('maintenance/schedules', $data);
    }

    public function addSchedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->maintenanceModel->addMaintenanceSchedule($_POST)) {
                $_SESSION['success'] = 'Maintenance schedule added successfully';
                $this->redirect('maintenance/schedules');
            } else {
                $_SESSION['error'] = 'Failed to add schedule';
            }
        }

        $vehicles = $this->vehicleModel->getAllVehicles();
        $maintenanceTypes = $this->maintenanceModel->getAllMaintenanceTypes();

        $data = [
            'vehicles' => $vehicles,
            'maintenance_types' => $maintenanceTypes,
            'active_menu' => 'maintenance',
            'page_title' => 'Add Maintenance Schedule'
        ];

        $this->view('maintenance/add_schedule', $data);
    }
}
