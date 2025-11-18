<?php
/**
 * Financial Model
 */

class Financial extends Database {

    public function __construct() {
        parent::__construct();
    }

    public function getAllAccounts() {
        $this->query('SELECT * FROM accounts ORDER BY account_name');
        return $this->fetchAll();
    }

    public function addAccount($data) {
        $this->query('INSERT INTO accounts (
            account_number, account_name, account_type, currency, balance,
            bank_name, iban, swift, status
        ) VALUES (
            :account_number, :account_name, :account_type, :currency, :balance,
            :bank_name, :iban, :swift, :status
        )');

        $this->bind(':account_number', $data['account_number']);
        $this->bind(':account_name', $data['account_name']);
        $this->bind(':account_type', $data['account_type']);
        $this->bind(':currency', $data['currency'] ?? 'TND');
        $this->bind(':balance', $data['balance'] ?? 0);
        $this->bind(':bank_name', $data['bank_name'] ?? null);
        $this->bind(':iban', $data['iban'] ?? null);
        $this->bind(':swift', $data['swift'] ?? null);
        $this->bind(':status', $data['status'] ?? 'active');

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function getAllTransactions($limit = 100) {
        $this->query('SELECT t.*, a.account_name
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            ORDER BY t.transaction_date DESC, t.created_at DESC
            LIMIT :limit');
        $this->bind(':limit', $limit);
        return $this->fetchAll();
    }

    public function addTransaction($data) {
        $this->query('INSERT INTO transactions (
            transaction_date, account_id, type, category, amount, description,
            reference, invoice_id, work_order_id, payment_method, created_by
        ) VALUES (
            :transaction_date, :account_id, :type, :category, :amount, :description,
            :reference, :invoice_id, :work_order_id, :payment_method, :created_by
        )');

        $this->bind(':transaction_date', $data['transaction_date']);
        $this->bind(':account_id', $data['account_id']);
        $this->bind(':type', $data['type']);
        $this->bind(':category', $data['category']);
        $this->bind(':amount', $data['amount']);
        $this->bind(':description', $data['description'] ?? null);
        $this->bind(':reference', $data['reference'] ?? null);
        $this->bind(':invoice_id', $data['invoice_id'] ?? null);
        $this->bind(':work_order_id', $data['work_order_id'] ?? null);
        $this->bind(':payment_method', $data['payment_method'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            // Update account balance
            $multiplier = $data['type'] === 'income' ? 1 : -1;
            $this->query('UPDATE accounts SET balance = balance + :amount WHERE id = :account_id');
            $this->bind(':amount', $data['amount'] * $multiplier);
            $this->bind(':account_id', $data['account_id']);
            $this->execute();

            return true;
        }
        return false;
    }

    public function getFinancialSummary($startDate = null, $endDate = null) {
        $sql = 'SELECT
            SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expenses,
            SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as net_balance
            FROM transactions WHERE 1=1';

        if ($startDate && $endDate) {
            $sql .= ' AND transaction_date BETWEEN :start_date AND :end_date';
        }

        $this->query($sql);

        if ($startDate && $endDate) {
            $this->bind(':start_date', $startDate);
            $this->bind(':end_date', $endDate);
        }

        return $this->fetch();
    }
}
