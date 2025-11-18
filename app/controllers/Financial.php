<?php
/**
 * Financial Controller
 */

class Financial extends Controller {

    private $financialModel;

    public function __construct() {
        $this->financialModel = $this->model('Financial');
    }

    public function index() {
        $accounts = $this->financialModel->getAllAccounts();
        $transactions = $this->financialModel->getAllTransactions(50);
        $summary = $this->financialModel->getFinancialSummary();

        $data = [
            'accounts' => $accounts,
            'transactions' => $transactions,
            'summary' => $summary,
            'active_menu' => 'financial',
            'page_title' => 'Financial Management'
        ];

        $this->view('financial/index', $data);
    }

    public function accounts() {
        $accounts = $this->financialModel->getAllAccounts();

        $data = [
            'accounts' => $accounts,
            'active_menu' => 'financial',
            'page_title' => 'Accounts'
        ];

        $this->view('financial/accounts', $data);
    }

    public function addTransaction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->financialModel->addTransaction($_POST)) {
                $_SESSION['success'] = 'Transaction added successfully';
                $this->redirect('financial');
            } else {
                $_SESSION['error'] = 'Failed to add transaction';
            }
        }

        $accounts = $this->financialModel->getAllAccounts();

        $data = [
            'accounts' => $accounts,
            'active_menu' => 'financial',
            'page_title'] = 'Add Transaction'
        ];

        $this->view('financial/add_transaction', $data);
    }
}
