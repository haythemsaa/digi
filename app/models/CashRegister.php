<?php
/**
 * Cash Register Model - Multi-tenant enabled
 * Manages cash registers, operations, checks, and bank reconciliations
 */

class CashRegister extends Model {
    private $companyId;

    public function __construct() {
        parent::__construct();
        $this->companyId = getCurrentCompanyId();

        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    private function getCompanyFilter($tableAlias = '') {
        if (isSuperAdmin()) {
            return '1=1';
        }
        $prefix = $tableAlias ? "{$tableAlias}." : '';
        return "{$prefix}company_id = :company_id";
    }

    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    /**
     * Get all cash registers
     */
    public function getAllRegisters() {
        $companyFilter = $this->getCompanyFilter('cr');
        $this->db->query("SELECT cr.*, CONCAT(u.first_name, ' ', u.last_name) as manager_name
                         FROM cash_registers cr
                         LEFT JOIN users u ON cr.manager_id = u.id
                         WHERE cr.is_active = 1 AND {$companyFilter}
                         ORDER BY cr.name");
        $this->bindCompanyId();
        return $this->db->resultSet();
    }

    /**
     * Get register by ID
     */
    public function getRegisterById($id) {
        $companyFilter = $this->getCompanyFilter('cr');
        $this->db->query("SELECT cr.*, CONCAT(u.first_name, ' ', u.last_name) as manager_name
                         FROM cash_registers cr
                         LEFT JOIN users u ON cr.manager_id = u.id
                         WHERE cr.id = ? AND {$companyFilter}");
        $this->db->bind(1, $id);
        $this->bindCompanyId();
        return $this->db->single();
    }

    /**
     * Create cash register
     */
    public function createRegister($data) {
        $this->db->query("INSERT INTO cash_registers
                         (company_id, name, type, currency, opening_balance, current_balance,
                          location, manager_id, bank_name, account_number, is_active)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $openingBalance = floatval($data['opening_balance'] ?? 0);

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $data['name']);
        $this->db->bind(3, $data['type']);
        $this->db->bind(4, $data['currency'] ?? 'TND');
        $this->db->bind(5, $openingBalance);
        $this->db->bind(6, $openingBalance); // Current balance starts as opening balance
        $this->db->bind(7, $data['location'] ?? null);
        $this->db->bind(8, $data['manager_id'] ?? null);
        $this->db->bind(9, $data['bank_name'] ?? null);
        $this->db->bind(10, $data['account_number'] ?? null);
        $this->db->bind(11, $data['is_active'] ?? 1);

        return $this->db->execute();
    }

    /**
     * Update cash register
     */
    public function updateRegister($id, $data) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE cash_registers SET
                         name = ?, type = ?, currency = ?,
                         location = ?, manager_id = ?,
                         bank_name = ?, account_number = ?, is_active = ?
                         WHERE id = ? AND {$companyFilter}");

        $this->db->bind(1, $data['name']);
        $this->db->bind(2, $data['type']);
        $this->db->bind(3, $data['currency'] ?? 'TND');
        $this->db->bind(4, $data['location'] ?? null);
        $this->db->bind(5, $data['manager_id'] ?? null);
        $this->db->bind(6, $data['bank_name'] ?? null);
        $this->db->bind(7, $data['account_number'] ?? null);
        $this->db->bind(8, $data['is_active'] ?? 1);
        $this->db->bind(9, $id);
        $this->bindCompanyId();

        return $this->db->execute();
    }

    /**
     * Update register balance
     */
    private function updateRegisterBalance($registerId, $amount) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE cash_registers SET current_balance = current_balance + ? WHERE id = ? AND {$companyFilter}");
        $this->db->bind(1, $amount);
        $this->db->bind(2, $registerId);
        $this->bindCompanyId();
        return $this->db->execute();
    }

    // ==================== CASH OPERATIONS ====================

    /**
     * Get all cash operations
     */
    public function getAllOperations($filters = []) {
        $companyFilter = $this->getCompanyFilter('co');
        $sql = "SELECT co.*,
                       cr.name as register_name, cr.type as register_type,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       c.name as client_name,
                       s.name as supplier_name
                FROM cash_operations co
                LEFT JOIN cash_registers cr ON co.register_id = cr.id
                LEFT JOIN users u ON co.user_id = u.id
                LEFT JOIN clients c ON co.client_id = c.id
                LEFT JOIN suppliers s ON co.supplier_id = s.id
                WHERE {$companyFilter}";

        $params = [];

        if (!empty($filters['register_id'])) {
            $sql .= " AND co.register_id = ?";
            $params[] = $filters['register_id'];
        }

        if (!empty($filters['operation_type'])) {
            $sql .= " AND co.operation_type = ?";
            $params[] = $filters['operation_type'];
        }

        if (!empty($filters['category'])) {
            $sql .= " AND co.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND co.operation_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND co.operation_date <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY co.operation_date DESC, co.created_at DESC";

        $this->db->query($sql);
        $this->bindCompanyId();
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get operation by ID
     */
    public function getOperationById($id) {
        $companyFilter = $this->getCompanyFilter('co');
        $this->db->query("SELECT co.*,
                                 cr.name as register_name, cr.type as register_type,
                                 CONCAT(u.first_name, ' ', u.last_name) as user_name,
                                 c.name as client_name,
                                 s.name as supplier_name
                          FROM cash_operations co
                          LEFT JOIN cash_registers cr ON co.register_id = cr.id
                          LEFT JOIN users u ON co.user_id = u.id
                          LEFT JOIN clients c ON co.client_id = c.id
                          LEFT JOIN suppliers s ON co.supplier_id = s.id
                          WHERE co.id = ? AND {$companyFilter}");
        $this->db->bind(1, $id);
        $this->bindCompanyId();
        return $this->db->single();
    }

    /**
     * Add cash operation
     */
    public function addOperation($data) {
        // Generate operation number
        $operationNumber = $this->generateOperationNumber($data['operation_type']);

        $this->db->query("INSERT INTO cash_operations
                         (company_id, operation_number, register_id, operation_type, operation_date,
                          category, amount, payment_method, reference,
                          client_id, supplier_id, vehicle_id,
                          description, notes, user_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $operationNumber);
        $this->db->bind(3, $data['register_id']);
        $this->db->bind(4, $data['operation_type']);
        $this->db->bind(5, $data['operation_date'] ?? date('Y-m-d'));
        $this->db->bind(6, $data['category']);
        $this->db->bind(7, $data['amount']);
        $this->db->bind(8, $data['payment_method'] ?? 'cash');
        $this->db->bind(9, $data['reference'] ?? null);
        $this->db->bind(10, $data['client_id'] ?? null);
        $this->db->bind(11, $data['supplier_id'] ?? null);
        $this->db->bind(12, $data['vehicle_id'] ?? null);
        $this->db->bind(13, $data['description'] ?? null);
        $this->db->bind(14, $data['notes'] ?? null);
        $this->db->bind(15, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            // Update register balance
            $amount = floatval($data['amount']);
            if ($data['operation_type'] == 'expense') {
                $amount = -$amount; // Negative for expenses
            }
            $this->updateRegisterBalance($data['register_id'], $amount);

            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Generate operation number
     */
    private function generateOperationNumber($type) {
        $companyFilter = $this->getCompanyFilter('');
        $year = date('Y');
        $prefix = ($type == 'income' ? 'IN' : 'OUT') . '-' . $year . '-';

        $this->db->query("SELECT operation_number FROM cash_operations
                         WHERE operation_number LIKE ? AND {$companyFilter}
                         ORDER BY operation_number DESC LIMIT 1");
        $this->db->bind(1, $prefix . '%');
        $this->bindCompanyId();
        $result = $this->db->single();

        if ($result) {
            $lastNumber = intval(substr($result['operation_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Transfer between registers
     */
    public function transferBetweenRegisters($fromRegisterId, $toRegisterId, $amount, $description = null) {
        $transferNumber = 'TRF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Create expense operation for source register
        $expenseData = [
            'operation_number' => $transferNumber . '-OUT',
            'register_id' => $fromRegisterId,
            'operation_type' => 'expense',
            'operation_date' => date('Y-m-d'),
            'category' => 'transfer',
            'amount' => $amount,
            'payment_method' => 'transfer',
            'reference' => $transferNumber,
            'description' => $description ?? 'Transfer to another register',
            'user_id' => $_SESSION['user_id'] ?? null
        ];

        $this->db->query("INSERT INTO cash_operations
                         (company_id, operation_number, register_id, operation_type, operation_date,
                          category, amount, payment_method, reference, description, user_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $expenseData['operation_number']);
        $this->db->bind(3, $expenseData['register_id']);
        $this->db->bind(4, $expenseData['operation_type']);
        $this->db->bind(5, $expenseData['operation_date']);
        $this->db->bind(6, $expenseData['category']);
        $this->db->bind(7, $expenseData['amount']);
        $this->db->bind(8, $expenseData['payment_method']);
        $this->db->bind(9, $expenseData['reference']);
        $this->db->bind(10, $expenseData['description']);
        $this->db->bind(11, $expenseData['user_id']);

        if (!$this->db->execute()) {
            return false;
        }

        // Create income operation for destination register
        $incomeData = [
            'operation_number' => $transferNumber . '-IN',
            'register_id' => $toRegisterId,
            'operation_type' => 'income',
            'operation_date' => date('Y-m-d'),
            'category' => 'transfer',
            'amount' => $amount,
            'payment_method' => 'transfer',
            'reference' => $transferNumber,
            'description' => $description ?? 'Transfer from another register',
            'user_id' => $_SESSION['user_id'] ?? null
        ];

        $this->db->query("INSERT INTO cash_operations
                         (company_id, operation_number, register_id, operation_type, operation_date,
                          category, amount, payment_method, reference, description, user_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $incomeData['operation_number']);
        $this->db->bind(3, $incomeData['register_id']);
        $this->db->bind(4, $incomeData['operation_type']);
        $this->db->bind(5, $incomeData['operation_date']);
        $this->db->bind(6, $incomeData['category']);
        $this->db->bind(7, $incomeData['amount']);
        $this->db->bind(8, $incomeData['payment_method']);
        $this->db->bind(9, $incomeData['reference']);
        $this->db->bind(10, $incomeData['description']);
        $this->db->bind(11, $incomeData['user_id']);

        if (!$this->db->execute()) {
            return false;
        }

        // Update balances
        $this->updateRegisterBalance($fromRegisterId, -$amount);
        $this->updateRegisterBalance($toRegisterId, $amount);

        return true;
    }

    // ==================== CHECKS ====================

    /**
     * Get all checks
     */
    public function getAllChecks($filters = []) {
        $companyFilter = $this->getCompanyFilter('ch');
        $sql = "SELECT ch.*,
                       cr.name as register_name,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       c.name as client_name,
                       s.name as supplier_name
                FROM checks ch
                LEFT JOIN cash_registers cr ON ch.register_id = cr.id
                LEFT JOIN users u ON ch.user_id = u.id
                LEFT JOIN clients c ON ch.client_id = c.id
                LEFT JOIN suppliers s ON ch.supplier_id = s.id
                WHERE {$companyFilter}";

        $params = [];

        if (!empty($filters['register_id'])) {
            $sql .= " AND ch.register_id = ?";
            $params[] = $filters['register_id'];
        }

        if (!empty($filters['check_type'])) {
            $sql .= " AND ch.check_type = ?";
            $params[] = $filters['check_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND ch.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY ch.issue_date DESC";

        $this->db->query($sql);
        $this->bindCompanyId();
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get check by ID
     */
    public function getCheckById($id) {
        $companyFilter = $this->getCompanyFilter('ch');
        $this->db->query("SELECT ch.*,
                                 cr.name as register_name,
                                 CONCAT(u.first_name, ' ', u.last_name) as user_name,
                                 c.name as client_name,
                                 s.name as supplier_name
                          FROM checks ch
                          LEFT JOIN cash_registers cr ON ch.register_id = cr.id
                          LEFT JOIN users u ON ch.user_id = u.id
                          LEFT JOIN clients c ON ch.client_id = c.id
                          LEFT JOIN suppliers s ON ch.supplier_id = s.id
                          WHERE ch.id = ? AND {$companyFilter}");
        $this->db->bind(1, $id);
        $this->bindCompanyId();
        return $this->db->single();
    }

    /**
     * Add check
     */
    public function addCheck($data) {
        $this->db->query("INSERT INTO checks
                         (company_id, register_id, check_type, check_number, amount,
                          issue_date, due_date, bank_name, drawer_name,
                          client_id, supplier_id, status, notes, user_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $data['register_id']);
        $this->db->bind(3, $data['check_type']);
        $this->db->bind(4, $data['check_number']);
        $this->db->bind(5, $data['amount']);
        $this->db->bind(6, $data['issue_date']);
        $this->db->bind(7, $data['due_date']);
        $this->db->bind(8, $data['bank_name'] ?? null);
        $this->db->bind(9, $data['drawer_name'] ?? null);
        $this->db->bind(10, $data['client_id'] ?? null);
        $this->db->bind(11, $data['supplier_id'] ?? null);
        $this->db->bind(12, $data['status'] ?? 'pending');
        $this->db->bind(13, $data['notes'] ?? null);
        $this->db->bind(14, $_SESSION['user_id'] ?? null);

        return $this->db->execute();
    }

    /**
     * Update check status
     */
    public function updateCheckStatus($id, $status, $depositDate = null) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE checks SET status = ?, deposit_date = ? WHERE id = ? AND {$companyFilter}");
        $this->db->bind(1, $status);
        $this->db->bind(2, $depositDate);
        $this->db->bind(3, $id);
        $this->bindCompanyId();

        if ($this->db->execute()) {
            // If check is cashed/deposited, create cash operation
            if ($status == 'cashed' || $status == 'deposited') {
                $check = $this->getCheckById($id);
                if ($check) {
                    $operationType = ($check['check_type'] == 'received') ? 'income' : 'expense';
                    $amount = ($check['check_type'] == 'received') ? $check['amount'] : -$check['amount'];

                    $this->addOperation([
                        'register_id' => $check['register_id'],
                        'operation_type' => $operationType,
                        'operation_date' => $depositDate ?? date('Y-m-d'),
                        'category' => 'check',
                        'amount' => abs($check['amount']),
                        'payment_method' => 'check',
                        'reference' => $check['check_number'],
                        'client_id' => $check['client_id'],
                        'supplier_id' => $check['supplier_id'],
                        'description' => 'Check ' . $check['check_number'] . ' ' . $status
                    ]);
                }
            }

            return true;
        }

        return false;
    }

    // ==================== BANK RECONCILIATION ====================

    /**
     * Get all reconciliations
     */
    public function getAllReconciliations($filters = []) {
        $companyFilter = $this->getCompanyFilter('br');
        $sql = "SELECT br.*,
                       cr.name as register_name,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM bank_reconciliations br
                LEFT JOIN cash_registers cr ON br.register_id = cr.id
                LEFT JOIN users u ON br.user_id = u.id
                WHERE cr.type = 'bank' AND {$companyFilter}";

        $params = [];

        if (!empty($filters['register_id'])) {
            $sql .= " AND br.register_id = ?";
            $params[] = $filters['register_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND br.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY br.reconciliation_date DESC";

        $this->db->query($sql);
        $this->bindCompanyId();
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get reconciliation by ID
     */
    public function getReconciliationById($id) {
        $companyFilter = $this->getCompanyFilter('br');
        $this->db->query("SELECT br.*,
                                 cr.name as register_name,
                                 CONCAT(u.first_name, ' ', u.last_name) as user_name
                          FROM bank_reconciliations br
                          LEFT JOIN cash_registers cr ON br.register_id = cr.id
                          LEFT JOIN users u ON br.user_id = u.id
                          WHERE br.id = ? AND {$companyFilter}");
        $this->db->bind(1, $id);
        $this->bindCompanyId();
        return $this->db->single();
    }

    /**
     * Create bank reconciliation
     */
    public function createReconciliation($data) {
        $systemBalance = floatval($data['system_balance']);
        $bankBalance = floatval($data['bank_balance']);
        $difference = $bankBalance - $systemBalance;

        $this->db->query("INSERT INTO bank_reconciliations
                         (company_id, register_id, reconciliation_date, statement_date,
                          system_balance, bank_balance, difference,
                          outstanding_checks, deposits_in_transit,
                          bank_fees, notes, status, user_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $data['register_id']);
        $this->db->bind(3, $data['reconciliation_date']);
        $this->db->bind(4, $data['statement_date']);
        $this->db->bind(5, $systemBalance);
        $this->db->bind(6, $bankBalance);
        $this->db->bind(7, $difference);
        $this->db->bind(8, $data['outstanding_checks'] ?? 0);
        $this->db->bind(9, $data['deposits_in_transit'] ?? 0);
        $this->db->bind(10, $data['bank_fees'] ?? 0);
        $this->db->bind(11, $data['notes'] ?? null);
        $this->db->bind(12, $_SESSION['user_id'] ?? null);

        return $this->db->execute();
    }

    /**
     * Complete reconciliation
     */
    public function completeReconciliation($id) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE bank_reconciliations SET status = 'completed' WHERE id = ? AND {$companyFilter}");
        $this->db->bind(1, $id);
        $this->bindCompanyId();
        return $this->db->execute();
    }

    /**
     * Get register cash flow
     */
    public function getRegisterCashFlow($registerId, $fromDate, $toDate) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("SELECT
                             SUM(CASE WHEN operation_type = 'income' THEN amount ELSE 0 END) as total_income,
                             SUM(CASE WHEN operation_type = 'expense' THEN amount ELSE 0 END) as total_expense,
                             COUNT(*) as operation_count
                          FROM cash_operations
                          WHERE register_id = ?
                          AND operation_date BETWEEN ? AND ?
                          AND {$companyFilter}");
        $this->db->bind(1, $registerId);
        $this->db->bind(2, $fromDate);
        $this->db->bind(3, $toDate);
        $this->bindCompanyId();

        return $this->db->single();
    }

    /**
     * Get cash statistics
     */
    public function getCashStats() {
        $companyFilter = $this->getCompanyFilter('');
        $stats = [];

        // Total cash balance
        $this->db->query("SELECT SUM(current_balance) as total
                         FROM cash_registers
                         WHERE type = 'cash' AND is_active = 1 AND {$companyFilter}");
        $this->bindCompanyId();
        $result = $this->db->single();
        $stats['total_cash'] = $result['total'] ?? 0;

        // Total bank balance
        $this->db->query("SELECT SUM(current_balance) as total
                         FROM cash_registers
                         WHERE type = 'bank' AND is_active = 1 AND {$companyFilter}");
        $this->bindCompanyId();
        $result = $this->db->single();
        $stats['total_bank'] = $result['total'] ?? 0;

        // Pending checks
        $this->db->query("SELECT COUNT(*) as count, SUM(amount) as total
                         FROM checks
                         WHERE status = 'pending' AND {$companyFilter}");
        $this->bindCompanyId();
        $result = $this->db->single();
        $stats['pending_checks_count'] = $result['count'];
        $stats['pending_checks_amount'] = $result['total'] ?? 0;

        // Today's income
        $this->db->query("SELECT SUM(amount) as total
                         FROM cash_operations
                         WHERE operation_type = 'income'
                         AND operation_date = CURRENT_DATE
                         AND {$companyFilter}");
        $this->bindCompanyId();
        $result = $this->db->single();
        $stats['today_income'] = $result['total'] ?? 0;

        // Today's expenses
        $this->db->query("SELECT SUM(amount) as total
                         FROM cash_operations
                         WHERE operation_type = 'expense'
                         AND operation_date = CURRENT_DATE
                         AND {$companyFilter}");
        $this->bindCompanyId();
        $result = $this->db->single();
        $stats['today_expense'] = $result['total'] ?? 0;

        return $stats;
    }
}
