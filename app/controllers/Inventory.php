<?php
/**
 * Inventory Controller
 */

class Inventory extends Controller {

    private $inventoryModel;

    public function __construct() {
        $this->inventoryModel = $this->model('Inventory');
    }

    public function index() {
        $parts = $this->inventoryModel->getAllParts();
        $lowStock = $this->inventoryModel->getLowStockParts();

        $data = [
            'parts' => $parts,
            'low_stock' => $lowStock,
            'active_menu' => 'inventory',
            'page_title' => 'Inventory & Stock Management'
        ];

        $this->view('inventory/index', $data);
    }

    public function addPart() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->inventoryModel->addPart($_POST)) {
                $_SESSION['success'] = 'Part added successfully';
                $this->redirect('inventory');
            } else {
                $_SESSION['error'] = 'Failed to add part';
            }
        }

        $data = [
            'active_menu' => 'inventory',
            'page_title' => 'Add Part'
        ];

        $this->view('inventory/add_part', $data);
    }

    public function movements() {
        $movements = $this->inventoryModel->getStockMovements();

        $data = [
            'movements' => $movements,
            'active_menu' => 'inventory',
            'page_title' => 'Stock Movements'
        ];

        $this->view('inventory/movements', $data);
    }

    public function addMovement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->inventoryModel->addStockMovement($_POST)) {
                $_SESSION['success'] = 'Stock movement recorded successfully';
                $this->redirect('inventory/movements');
            } else {
                $_SESSION['error'] = 'Failed to record stock movement';
            }
        }

        $parts = $this->inventoryModel->getAllParts();

        $data = [
            'parts' => $parts,
            'active_menu' => 'inventory',
            'page_title' => 'Record Stock Movement'
        ];

        $this->view('inventory/add_movement', $data);
    }
}
