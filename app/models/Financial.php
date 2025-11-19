<?php
/**
 * Financial Model - Multi-tenant enabled
 */

class Financial {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();

        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    private function getCompanyFilter($tableAlias = 'a') {
        if (isSuperAdmin()) {
            return '1=1';
        }
        return "{$tableAlias}.company_id = :company_id";
    }

    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    // ========== ACCOUNTS ==========

    public function getAllAccounts() {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("SELECT * FROM accounts a
            WHERE {$filter}
            ORDER BY a.account_name");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getAccountById($id) {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("SELECT * FROM accounts a WHERE a.id = :id AND {$filter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();

        return $this->db->fetch();
    }

    public function addAccount($data) {
        $this->db->query('INSERT INTO accounts (
            company_id, account_number, account_name, account_type, currency, balance,
            bank_name, iban, swift, status
        ) VALUES (
            :company_id, :account_number, :account_name, :account_type, :currency, :balance,
            :bank_name, :iban, :swift, :status
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':account_number', $data['account_number']);
        $this->db->bind(':account_name', $data['account_name']);
        $this->db->bind(':account_type', $data['account_type']);
        $this->db->bind(':currency', $data['currency'] ?? 'TND');
        $this->db->bind(':balance', $data['balance'] ?? 0);
        $this->db->bind(':bank_name', $data['bank_name'] ?? null);
        $this->db->bind(':iban', $data['iban'] ?? null);
        $this->db->bind(':swift', $data['swift'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateAccount($id, $data) {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("UPDATE accounts a SET
            a.account_name = :account_name,
            a.account_type = :account_type,
            a.bank_name = :bank_name,
            a.iban = :iban,
            a.swift = :swift,
            a.status = :status
            WHERE a.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':account_name', $data['account_name']);
        $this->db->bind(':account_type', $data['account_type']);
        $this->db->bind(':bank_name', $data['bank_name'] ?? null);
        $this->db->bind(':iban', $data['iban'] ?? null);
        $this->db->bind(':swift', $data['swift'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');

        return $this->db->execute();
    }

    // ========== TRANSACTIONS ==========

    public function getAllTransactions($limit = 100) {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("SELECT t.*, a.account_name
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            WHERE {$filter}
            ORDER BY t.transaction_date DESC, t.created_at DESC
            LIMIT :limit");

        $this->bindCompanyId();
        $this->db->bind(':limit', $limit);
        return $this->db->fetchAll();
    }

    public function getTransactionById($id) {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("SELECT t.*, a.account_name
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            WHERE t.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();

        return $this->db->fetch();
    }

    public function getTransactionsByAccount($accountId, $limit = 100) {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("SELECT t.*, a.account_name
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            WHERE t.account_id = :account_id AND {$filter}
            ORDER BY t.transaction_date DESC
            LIMIT :limit");

        $this->db->bind(':account_id', $accountId);
        $this->bindCompanyId();
        $this->db->bind(':limit', $limit);

        return $this->db->fetchAll();
    }

    public function addTransaction($data) {
        // Verify account belongs to company
        $account = $this->getAccountById($data['account_id']);
        if (!$account) {
            return false;
        }

        $this->db->query('INSERT INTO transactions (
            transaction_date, account_id, type, category, amount, description,
            reference, invoice_id, work_order_id, payment_method, created_by
        ) VALUES (
            :transaction_date, :account_id, :type, :category, :amount, :description,
            :reference, :invoice_id, :work_order_id, :payment_method, :created_by
        )');

        $this->db->bind(':transaction_date', $data['transaction_date']);
        $this->db->bind(':account_id', $data['account_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':reference', $data['reference'] ?? null);
        $this->db->bind(':invoice_id', $data['invoice_id'] ?? null);
        $this->db->bind(':work_order_id', $data['work_order_id'] ?? null);
        $this->db->bind(':payment_method', $data['payment_method'] ?? null);
        $this->db->bind(':created_by', $_SESSION['user_id']);

        if ($this->db->execute()) {
            // Update account balance
            $multiplier = $data['type'] === 'income' ? 1 : -1;

            $filter = $this->getCompanyFilter('a');
            $this->db->query("UPDATE accounts a SET a.balance = a.balance + :amount
                WHERE a.id = :account_id AND {$filter}");

            $this->db->bind(':amount', $data['amount'] * $multiplier);
            $this->db->bind(':account_id', $data['account_id']);
            $this->bindCompanyId();
            $this->db->execute();

            return true;
        }
        return false;
    }

    // ========== REPORTING & ANALYTICS ==========

    public function getFinancialSummary($startDate = null, $endDate = null) {
        $filter = $this->getCompanyFilter('a');

        $sql = "SELECT
            SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as total_income,
            SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) as total_expenses,
            SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE -t.amount END) as net_balance
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            WHERE {$filter}";

        if ($startDate && $endDate) {
            $sql .= ' AND t.transaction_date BETWEEN :start_date AND :end_date';
        }

        $this->db->query($sql);
        $this->bindCompanyId();

        if ($startDate && $endDate) {
            $this->db->bind(':start_date', $startDate);
            $this->db->bind(':end_date', $endDate);
        }

        return $this->db->fetch();
    }

    public function getCashFlow($months = 12) {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("SELECT
            DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
            SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as income,
            SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) as expenses,
            SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE -t.amount END) as net
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            WHERE {$filter}
            AND t.transaction_date >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY month
            ORDER BY month ASC");

        $this->bindCompanyId();
        $this->db->bind(':months', $months);

        return $this->db->fetchAll();
    }

    public function getExpensesByCategory($startDate = null, $endDate = null) {
        $filter = $this->getCompanyFilter('a');

        $sql = "SELECT
            t.category,
            SUM(t.amount) as total,
            COUNT(*) as count
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            WHERE {$filter}
            AND t.type = 'expense'";

        if ($startDate && $endDate) {
            $sql .= ' AND t.transaction_date BETWEEN :start_date AND :end_date';
        }

        $sql .= ' GROUP BY t.category ORDER BY total DESC';

        $this->db->query($sql);
        $this->bindCompanyId();

        if ($startDate && $endDate) {
            $this->db->bind(':start_date', $startDate);
            $this->db->bind(':end_date', $endDate);
        }

        return $this->db->fetchAll();
    }

    public function getTotalBalance() {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("SELECT SUM(a.balance) as total_balance
            FROM accounts a
            WHERE {$filter} AND a.status = 'active'");

        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $result['total_balance'] ?? 0;
    }

    public function getDashboardStats() {
        $filter = $this->getCompanyFilter('a');

        $this->db->query("SELECT
            COUNT(DISTINCT a.id) as total_accounts,
            SUM(a.balance) as total_balance,
            (SELECT COUNT(*) FROM transactions t2
             LEFT JOIN accounts a2 ON t2.account_id = a2.id
             WHERE {$filter} AND t2.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as transactions_last_30_days,
            (SELECT SUM(t3.amount) FROM transactions t3
             LEFT JOIN accounts a3 ON t3.account_id = a3.id
             WHERE {$filter} AND t3.type = 'income' AND t3.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as income_last_30_days,
            (SELECT SUM(t4.amount) FROM transactions t4
             LEFT JOIN accounts a4 ON t4.account_id = a4.id
             WHERE {$filter} AND t4.type = 'expense' AND t4.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as expenses_last_30_days
            FROM accounts a
            WHERE {$filter}");

        $this->bindCompanyId();
        return $this->db->fetch();
    }
}
