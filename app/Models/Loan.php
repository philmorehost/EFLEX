<?php
namespace Models;

use Core\Database;

class Loan {
    private $db;
    private $transactionModel;

    public function __construct() {
        $this->db = new Database;
        $this->transactionModel = new Transaction();
    }

    /**
     * Checks if a user is eligible for a loan and determines the max amount.
     * This is a simple implementation; a real-world scenario would be more complex.
     * @param int $user_id The user's ID.
     * @return array An array containing eligibility status, max loan amount, and a message.
     */
    public function checkEligibility($user_id) {
        // Rule 1: User must not have an active or pending loan.
        $this->db->query("SELECT id FROM loans WHERE user_id = :user_id AND status IN ('pending', 'active')");
        $this->db->bind(':user_id', $user_id);
        $this->db->single();
        if ($this->db->rowCount() > 0) {
            return ['is_eligible' => false, 'max_loan_amount' => 0, 'message' => 'You already have a pending or active loan.'];
        }

        // Rule 2: User must have made at least 5 transactions.
        $this->db->query('SELECT COUNT(id) as transaction_count FROM transactions WHERE user_id = :user_id');
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        $transaction_count = $row->transaction_count;

        if ($transaction_count < 5) {
            return ['is_eligible' => false, 'max_loan_amount' => 0, 'message' => 'You need at least 5 transactions to be eligible for a loan.'];
        }

        // Rule 3: Max loan amount is 20% of total deposits.
        $this->db->query("SELECT SUM(amount) as total_deposits FROM transactions WHERE user_id = :user_id AND service = 'deposit' AND status = 'successful'");
        $this->db->bind(':user_id', $user_id);
        $deposit_row = $this->db->single();
        $total_deposits = $deposit_row->total_deposits ?? 0;

        $max_loan_amount = floor($total_deposits * 0.20);

        if ($max_loan_amount < 1000) {
             return ['is_eligible' => false, 'max_loan_amount' => 0, 'message' => 'Your transaction history does not make you eligible for a loan at this time.'];
        }

        return ['is_eligible' => true, 'max_loan_amount' => $max_loan_amount, 'message' => 'You are eligible to apply for a loan up to ₦' . number_format($max_loan_amount, 2)];
    }

    /**
     * Creates a new loan application.
     * @param int $user_id The user's ID.
     * @param float $amount The requested loan amount.
     * @return array An array indicating success status and a message.
     */
    public function applyForLoan($user_id, $amount) {
        $eligibility = $this->checkEligibility($user_id);

        if (!$eligibility['is_eligible']) {
            return ['success' => false, 'message' => $eligibility['message']];
        }

        if ($amount > $eligibility['max_loan_amount']) {
            return ['success' => false, 'message' => 'The requested amount exceeds your maximum loan limit of ₦' . number_format($eligibility['max_loan_amount'], 2)];
        }

        $this->db->query('INSERT INTO loans (user_id, amount_requested) VALUES (:user_id, :amount)');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':amount', $amount);

        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Application successful.'];
        } else {
            return ['success' => false, 'message' => 'Could not process loan application at this time.'];
        }
    }

    /**
     * Gets a user's active or pending loan.
     * @param int $user_id The user's ID.
     * @return object|false The loan object or false if none exists.
     */
    public function getActiveLoanByUserId($user_id) {
        $this->db->query("SELECT * FROM loans WHERE user_id = :user_id AND status IN ('pending', 'active')");
        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    // --- Admin Methods ---

    public function getAllPendingLoans() {
        $this->db->query("SELECT loans.*, users.username FROM loans JOIN users ON loans.user_id = users.id WHERE loans.status = 'pending' ORDER BY loans.created_at ASC");
        return $this->db->resultSet();
    }

    public function approve($loan_id) {
        $this->db->query('SELECT * FROM loans WHERE id = :loan_id AND status = "pending"');
        $this->db->bind(':loan_id', $loan_id);
        $loan = $this->db->single();

        if (!$loan) {
            return false; // Loan not found or not pending
        }

        // Credit user's main wallet
        $userModel = new User();
        if ($userModel->creditWallet($loan->user_id, $loan->amount_requested)) {
            // Update loan status to active and set due date
            $due_date = date('Y-m-d', strtotime("+{$loan->tenure_days} days"));
            $this->db->query("UPDATE loans SET status = 'active', due_date = :due_date WHERE id = :loan_id");
            $this->db->bind(':due_date', $due_date);
            $this->db->bind(':loan_id', $loan_id);

            return $this->db->execute();
        }

        return false; // Failed to credit wallet
    }

    public function deny($loan_id) {
        $this->db->query("UPDATE loans SET status = 'denied' WHERE id = :loan_id AND status = 'pending'");
        $this->db->bind(':loan_id', $loan_id);

        return $this->db->execute();
    }

    public function makeRepayment($loan_id, $user_id, $amount) {
        $this->db->query('SELECT * FROM loans WHERE id = :loan_id AND user_id = :user_id AND status = "active"');
        $this->db->bind(':loan_id', $loan_id);
        $this->db->bind(':user_id', $user_id);
        $loan = $this->db->single();

        if (!$loan) {
            return ['success' => false, 'message' => 'No active loan found to repay.'];
        }

        $total_due = ($loan->amount_requested * (1 + $loan->interest_rate / 100));
        $outstanding_balance = $total_due - $loan->amount_repaid;

        if ($amount > $outstanding_balance) {
            return ['success' => false, 'message' => 'Repayment amount cannot be more than the outstanding balance of ₦' . number_format($outstanding_balance, 2)];
        }

        // 1. Debit user's wallet
        $userModel = new User();
        if (!$userModel->debitWallet($user_id, $amount)) {
            return ['success' => false, 'message' => 'Insufficient wallet balance for repayment.'];
        }

        // 2. Log the repayment
        $this->db->query('INSERT INTO loan_repayments (loan_id, user_id, amount) VALUES (:loan_id, :user_id, :amount)');
        $this->db->bind(':loan_id', $loan_id);
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':amount', $amount);

        if (!$this->db->execute()) {
            // Critical failure: Revert wallet debit
            $userModel->creditWallet($user_id, $amount);
            return ['success' => false, 'message' => 'Could not process repayment at this time (LRP:01).'];
        }

        // 3. Update the main loan record
        $new_amount_repaid = $loan->amount_repaid + $amount;
        $new_status = ($new_amount_repaid >= $total_due) ? 'repaid' : 'active';

        $this->db->query("UPDATE loans SET amount_repaid = :amount_repaid, status = :status WHERE id = :loan_id");
        $this->db->bind(':amount_repaid', $new_amount_repaid);
        $this->db->bind(':status', $new_status);
        $this->db->bind(':loan_id', $loan_id);

        if (!$this->db->execute()) {
            // This is also a critical failure, but harder to revert the repayment log.
            // A full transaction-based system would be better here. For now, we log it.
            error_log("CRITICAL: Failed to update main loan record for ID {$loan_id} after repayment.");
            return ['success' => false, 'message' => 'Could not process repayment at this time (LRP:02).'];
        }

        return ['success' => true, 'message' => 'Repayment successful.'];
    }
}