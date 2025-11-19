<?php
/**
 * Cash Controller
 * Manages cash registers, operations, checks, and bank reconciliations
 */

class Cash extends Controller {
    private $cashModel;

    public function __construct() {
        $this->cashModel = $this->model('CashRegister');
    }

    /**
     * Index - Cash management dashboard
     */
    public function index() {
        $registers = $this->cashModel->getAllRegisters();
        $stats = $this->cashModel->getCashStats();

        // Get recent operations
        $recentOperations = $this->cashModel->getAllOperations([]);
        $recentOperations = array_slice($recentOperations, 0, 10); // Last 10 operations

        $data = [
            'page_title' => 'Cash Management',
            'registers' => $registers,
            'stats' => $stats,
            'recent_operations' => $recentOperations
        ];

        $this->view('cash/index', $data);
    }

    // ==================== CASH REGISTERS ====================

    /**
     * Cash registers list
     */
    public function registers() {
        $registers = $this->cashModel->getAllRegisters();

        $data = [
            'page_title' => 'Cash Registers',
            'registers' => $registers
        ];

        $this->view('cash/registers', $data);
    }

    /**
     * Add cash register
     */
    public function addRegister() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->cashModel->createRegister($_POST)) {
                $_SESSION['success'] = 'Cash register created successfully';
                $this->redirect('cash/registers');
            } else {
                $_SESSION['error'] = 'Failed to create cash register';
            }
        }

        $userModel = $this->model('User');

        $data = [
            'page_title' => 'New Cash Register',
            'users' => $userModel->getAllUsers()
        ];

        $this->view('cash/add_register', $data);
    }

    /**
     * Edit cash register
     */
    public function editRegister($id) {
        $register = $this->cashModel->getRegisterById($id);

        if (!$register) {
            $_SESSION['error'] = 'Cash register not found';
            $this->redirect('cash/registers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->cashModel->updateRegister($id, $_POST)) {
                $_SESSION['success'] = 'Cash register updated successfully';
                $this->redirect('cash/registers');
            } else {
                $_SESSION['error'] = 'Failed to update cash register';
            }
        }

        $userModel = $this->model('User');

        $data = [
            'page_title' => 'Edit Cash Register',
            'register' => $register,
            'users' => $userModel->getAllUsers()
        ];

        $this->view('cash/edit_register', $data);
    }

    /**
     * View register details
     */
    public function viewRegister($id) {
        $register = $this->cashModel->getRegisterById($id);

        if (!$register) {
            $_SESSION['error'] = 'Cash register not found';
            $this->redirect('cash/registers');
        }

        // Get operations for this register
        $operations = $this->cashModel->getAllOperations(['register_id' => $id]);

        // Calculate cash flow
        $fromDate = $_GET['from_date'] ?? date('Y-m-01'); // First day of current month
        $toDate = $_GET['to_date'] ?? date('Y-m-d'); // Today
        $cashFlow = $this->cashModel->getRegisterCashFlow($id, $fromDate, $toDate);

        $data = [
            'page_title' => 'Cash Register Details',
            'register' => $register,
            'operations' => $operations,
            'cash_flow' => $cashFlow,
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];

        $this->view('cash/view_register', $data);
    }

    // ==================== CASH OPERATIONS ====================

    /**
     * Cash operations list
     */
    public function operations() {
        $filters = [];
        if (isset($_GET['register_id'])) {
            $filters['register_id'] = $_GET['register_id'];
        }
        if (isset($_GET['operation_type'])) {
            $filters['operation_type'] = $_GET['operation_type'];
        }
        if (isset($_GET['category'])) {
            $filters['category'] = $_GET['category'];
        }
        if (isset($_GET['from_date'])) {
            $filters['from_date'] = $_GET['from_date'];
        }
        if (isset($_GET['to_date'])) {
            $filters['to_date'] = $_GET['to_date'];
        }

        $operations = $this->cashModel->getAllOperations($filters);
        $registers = $this->cashModel->getAllRegisters();

        $data = [
            'page_title' => 'Cash Operations',
            'operations' => $operations,
            'registers' => $registers
        ];

        $this->view('cash/operations', $data);
    }

    /**
     * Add cash operation
     */
    public function addOperation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->cashModel->addOperation($_POST)) {
                $_SESSION['success'] = 'Cash operation added successfully';
                $this->redirect('cash/operations');
            } else {
                $_SESSION['error'] = 'Failed to add cash operation';
            }
        }

        $registers = $this->cashModel->getAllRegisters();
        $clientModel = $this->model('Client');
        $supplierModel = $this->model('Supplier');
        $vehicleModel = $this->model('Vehicle');

        $data = [
            'page_title' => 'New Cash Operation',
            'registers' => $registers,
            'clients' => $clientModel->getAllClients(),
            'suppliers' => $supplierModel->getAllSuppliers(),
            'vehicles' => $vehicleModel->getAllVehicles()
        ];

        $this->view('cash/add_operation', $data);
    }

    /**
     * View cash operation
     */
    public function viewOperation($id) {
        $operation = $this->cashModel->getOperationById($id);

        if (!$operation) {
            $_SESSION['error'] = 'Cash operation not found';
            $this->redirect('cash/operations');
        }

        $data = [
            'page_title' => 'Cash Operation Details',
            'operation' => $operation
        ];

        $this->view('cash/view_operation', $data);
    }

    /**
     * Transfer between registers
     */
    public function transfer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fromRegisterId = $_POST['from_register_id'] ?? null;
            $toRegisterId = $_POST['to_register_id'] ?? null;
            $amount = $_POST['amount'] ?? 0;
            $description = $_POST['description'] ?? null;

            if ($fromRegisterId == $toRegisterId) {
                $_SESSION['error'] = 'Cannot transfer to the same register';
            } elseif ($amount <= 0) {
                $_SESSION['error'] = 'Amount must be greater than zero';
            } else {
                if ($this->cashModel->transferBetweenRegisters($fromRegisterId, $toRegisterId, $amount, $description)) {
                    $_SESSION['success'] = 'Transfer completed successfully';
                    $this->redirect('cash');
                } else {
                    $_SESSION['error'] = 'Failed to complete transfer';
                }
            }
        }

        $registers = $this->cashModel->getAllRegisters();

        $data = [
            'page_title' => 'Transfer Between Registers',
            'registers' => $registers
        ];

        $this->view('cash/transfer', $data);
    }

    // ==================== CHECKS ====================

    /**
     * Checks list
     */
    public function checks() {
        $filters = [];
        if (isset($_GET['register_id'])) {
            $filters['register_id'] = $_GET['register_id'];
        }
        if (isset($_GET['check_type'])) {
            $filters['check_type'] = $_GET['check_type'];
        }
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $checks = $this->cashModel->getAllChecks($filters);
        $registers = $this->cashModel->getAllRegisters();

        $data = [
            'page_title' => 'Checks Management',
            'checks' => $checks,
            'registers' => $registers
        ];

        $this->view('cash/checks', $data);
    }

    /**
     * Add check
     */
    public function addCheck() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->cashModel->addCheck($_POST)) {
                $_SESSION['success'] = 'Check added successfully';
                $this->redirect('cash/checks');
            } else {
                $_SESSION['error'] = 'Failed to add check';
            }
        }

        $registers = $this->cashModel->getAllRegisters();
        $clientModel = $this->model('Client');
        $supplierModel = $this->model('Supplier');

        $data = [
            'page_title' => 'New Check',
            'registers' => $registers,
            'clients' => $clientModel->getAllClients(),
            'suppliers' => $supplierModel->getAllSuppliers()
        ];

        $this->view('cash/add_check', $data);
    }

    /**
     * View check details
     */
    public function viewCheck($id) {
        $check = $this->cashModel->getCheckById($id);

        if (!$check) {
            $_SESSION['error'] = 'Check not found';
            $this->redirect('cash/checks');
        }

        $data = [
            'page_title' => 'Check Details',
            'check' => $check
        ];

        $this->view('cash/view_check', $data);
    }

    /**
     * Update check status
     */
    public function updateCheckStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            $depositDate = $_POST['deposit_date'] ?? null;

            if ($this->cashModel->updateCheckStatus($id, $status, $depositDate)) {
                $_SESSION['success'] = 'Check status updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update check status';
            }
        }

        $this->redirect('cash/viewCheck/' . $id);
    }

    // ==================== BANK RECONCILIATION ====================

    /**
     * Bank reconciliations list
     */
    public function reconciliations() {
        $filters = [];
        if (isset($_GET['register_id'])) {
            $filters['register_id'] = $_GET['register_id'];
        }
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $reconciliations = $this->cashModel->getAllReconciliations($filters);
        $registers = $this->cashModel->getAllRegisters();

        $data = [
            'page_title' => 'Bank Reconciliations',
            'reconciliations' => $reconciliations,
            'registers' => $registers
        ];

        $this->view('cash/reconciliations', $data);
    }

    /**
     * Add bank reconciliation
     */
    public function addReconciliation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->cashModel->createReconciliation($_POST)) {
                $_SESSION['success'] = 'Bank reconciliation created successfully';
                $this->redirect('cash/reconciliations');
            } else {
                $_SESSION['error'] = 'Failed to create bank reconciliation';
            }
        }

        // Get only bank type registers
        $registers = $this->cashModel->getAllRegisters();
        $bankRegisters = array_filter($registers, function($reg) {
            return $reg['type'] == 'bank';
        });

        $data = [
            'page_title' => 'New Bank Reconciliation',
            'registers' => $bankRegisters
        ];

        $this->view('cash/add_reconciliation', $data);
    }

    /**
     * View bank reconciliation
     */
    public function viewReconciliation($id) {
        $reconciliation = $this->cashModel->getReconciliationById($id);

        if (!$reconciliation) {
            $_SESSION['error'] = 'Bank reconciliation not found';
            $this->redirect('cash/reconciliations');
        }

        $data = [
            'page_title' => 'Bank Reconciliation Details',
            'reconciliation' => $reconciliation
        ];

        $this->view('cash/view_reconciliation', $data);
    }

    /**
     * Complete bank reconciliation
     */
    public function completeReconciliation($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->cashModel->completeReconciliation($id)) {
                $_SESSION['success'] = 'Bank reconciliation completed successfully';
            } else {
                $_SESSION['error'] = 'Failed to complete bank reconciliation';
            }
        }

        $this->redirect('cash/viewReconciliation/' . $id);
    }

    /**
     * Cash flow report
     */
    public function cashFlowReport() {
        $fromDate = $_GET['from_date'] ?? date('Y-m-01');
        $toDate = $_GET['to_date'] ?? date('Y-m-d');
        $registerId = $_GET['register_id'] ?? null;

        $filters = [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];

        if ($registerId) {
            $filters['register_id'] = $registerId;
        }

        $operations = $this->cashModel->getAllOperations($filters);
        $registers = $this->cashModel->getAllRegisters();

        // Calculate totals
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($operations as $op) {
            if ($op['operation_type'] == 'income') {
                $totalIncome += floatval($op['amount']);
            } else {
                $totalExpense += floatval($op['amount']);
            }
        }

        $netCashFlow = $totalIncome - $totalExpense;

        $data = [
            'page_title' => 'Cash Flow Report',
            'operations' => $operations,
            'registers' => $registers,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'selected_register' => $registerId,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_cash_flow' => $netCashFlow
        ];

        $this->view('cash/cash_flow_report', $data);
    }

    /**
     * Export cash flow report (CSV)
     */
    public function exportCashFlow() {
        $fromDate = $_GET['from_date'] ?? date('Y-m-01');
        $toDate = $_GET['to_date'] ?? date('Y-m-d');
        $registerId = $_GET['register_id'] ?? null;

        $filters = [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];

        if ($registerId) {
            $filters['register_id'] = $registerId;
        }

        $operations = $this->cashModel->getAllOperations($filters);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="cash_flow_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, [
            'Operation Number',
            'Date',
            'Register',
            'Type',
            'Category',
            'Amount',
            'Payment Method',
            'Description'
        ]);

        // Data
        foreach ($operations as $op) {
            fputcsv($output, [
                $op['operation_number'],
                $op['operation_date'],
                $op['register_name'],
                $op['operation_type'],
                $op['category'],
                number_format($op['amount'], 2),
                $op['payment_method'],
                $op['description']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get register balance (AJAX)
     */
    public function getRegisterBalance() {
        header('Content-Type: application/json');

        $registerId = $_GET['register_id'] ?? null;

        if ($registerId) {
            $register = $this->cashModel->getRegisterById($registerId);

            if ($register) {
                echo json_encode([
                    'success' => true,
                    'balance' => $register['current_balance'],
                    'currency' => $register['currency']
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Register not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing register ID']);
        }
        exit;
    }
}
