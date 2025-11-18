<?php
/**
 * Transport Management Controller
 */

class Transport extends Controller {

    private $transportModel;
    private $vehicleModel;
    private $userModel;

    public function __construct() {
        $this->transportModel = $this->model('Transport');
        $this->vehicleModel = $this->model('Vehicle');
        $this->userModel = $this->model('User');
    }

    public function index() {
        $orders = $this->transportModel->getAllOrders();

        $data = [
            'orders' => $orders,
            'active_menu' => 'transport',
            'page_title' => 'Transport Management'
        ];

        $this->view('transport/index', $data);
    }

    // ========== CLIENTS ==========
    public function clients() {
        $clients = $this->transportModel->getAllClients();

        $data = [
            'clients' => $clients,
            'active_menu' => 'transport',
            'page_title' => 'Clients'
        ];

        $this->view('transport/clients', $data);
    }

    public function addClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->transportModel->addClient($_POST)) {
                $_SESSION['success'] = 'Client added successfully';
                $this->redirect('transport/clients');
            } else {
                $_SESSION['error'] = 'Failed to add client';
            }
        }

        $data = [
            'active_menu' => 'transport',
            'page_title' => 'Add Client'
        ];

        $this->view('transport/add_client', $data);
    }

    // ========== QUOTES ==========
    public function quotes() {
        $quotes = $this->transportModel->getAllQuotes();

        $data = [
            'quotes' => $quotes,
            'active_menu' => 'transport',
            'page_title' => 'Transport Quotes'
        ];

        $this->view('transport/quotes', $data);
    }

    public function addQuote() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $_POST['quote_number'] = $this->transportModel->generateQuoteNumber();

            if ($this->transportModel->addQuote($_POST)) {
                $_SESSION['success'] = 'Quote created successfully';
                $this->redirect('transport/quotes');
            } else {
                $_SESSION['error'] = 'Failed to create quote';
            }
        }

        $clients = $this->transportModel->getAllClients();

        $data = [
            'clients' => $clients,
            'active_menu' => 'transport',
            'page_title' => 'Create Quote'
        ];

        $this->view('transport/add_quote', $data);
    }

    // ========== ORDERS ==========
    public function addOrder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $_POST['order_number'] = $this->transportModel->generateOrderNumber();

            if ($this->transportModel->addOrder($_POST)) {
                $_SESSION['success'] = 'Order created successfully';
                $this->redirect('transport');
            } else {
                $_SESSION['error'] = 'Failed to create order';
            }
        }

        $clients = $this->transportModel->getAllClients();
        $vehicles = $this->vehicleModel->getAllVehicles();
        $drivers = $this->userModel->getDrivers();

        $data = [
            'clients' => $clients,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'active_menu' => 'transport',
            'page_title' => 'Create Transport Order'
        ];

        $this->view('transport/add_order', $data);
    }

    public function viewOrder($id) {
        $order = $this->transportModel->getOrderById($id);

        if (!$order) {
            $_SESSION['error'] = 'Order not found';
            $this->redirect('transport');
        }

        $data = [
            'order' => $order,
            'active_menu' => 'transport',
            'page_title' => 'Order ' . $order['order_number']
        ];

        $this->view('transport/view_order', $data);
    }

    // ========== INVOICES ==========
    public function invoices() {
        $invoices = $this->transportModel->getAllInvoices();

        $data = [
            'invoices' => $invoices,
            'active_menu' => 'transport',
            'page_title' => 'Invoices'
        ];

        $this->view('transport/invoices', $data);
    }

    public function addInvoice() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $_POST['invoice_number'] = $this->transportModel->generateInvoiceNumber();

            if ($this->transportModel->addInvoice($_POST)) {
                $_SESSION['success'] = 'Invoice created successfully';
                $this->redirect('transport/invoices');
            } else {
                $_SESSION['error'] = 'Failed to create invoice';
            }
        }

        $clients = $this->transportModel->getAllClients();
        $orders = $this->transportModel->getAllOrders();

        $data = [
            'clients' => $clients,
            'orders' => $orders,
            'active_menu' => 'transport',
            'page_title' => 'Create Invoice'
        ];

        $this->view('transport/add_invoice', $data);
    }
}
