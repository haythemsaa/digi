<?php
/**
 * Procurement Controller
 */

class Procurement extends Controller {

    private $supplierModel;

    public function __construct() {
        $this->supplierModel = $this->model('Supplier');
    }

    public function index() {
        $suppliers = $this->supplierModel->getAllSuppliers();
        $purchaseOrders = $this->supplierModel->getPurchaseOrders();

        $data = [
            'suppliers' => $suppliers,
            'purchase_orders' => $purchaseOrders,
            'active_menu' => 'procurement',
            'page_title' => 'Procurement & Suppliers'
        ];

        $this->view('procurement/index', $data);
    }

    public function addSupplier() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->supplierModel->addSupplier($_POST)) {
                $_SESSION['success'] = 'Supplier added successfully';
                $this->redirect('procurement');
            } else {
                $_SESSION['error'] = 'Failed to add supplier';
            }
        }

        $data = [
            'active_menu' => 'procurement',
            'page_title' => 'Add Supplier'
        ];

        $this->view('procurement/add_supplier', $data);
    }

    public function addPurchaseOrder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $_POST['po_number'] = $this->supplierModel->generatePONumber();

            if ($this->supplierModel->addPurchaseOrder($_POST)) {
                $_SESSION['success'] = 'Purchase order created successfully';
                $this->redirect('procurement');
            } else {
                $_SESSION['error'] = 'Failed to create purchase order';
            }
        }

        $suppliers = $this->supplierModel->getAllSuppliers();

        $data = [
            'suppliers' => $suppliers,
            'active_menu' => 'procurement',
            'page_title' => 'Create Purchase Order'
        ];

        $this->view('procurement/add_purchase_order', $data);
    }
}
