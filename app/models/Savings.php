<?php
namespace Models;

use Core\Database;

class Savings {
    private $db;
    private $userModel;

    public function __construct() {
        $this->db = new Database;
        // We need the User model to debit the main wallet
        $this->userModel = new User();
    }

    /**
     * Gets a user's savings account by their ID.
     * If it doesn't exist, it creates one.
     * @param int $user_id The user's ID.
     * @return object The savings account object.
     */
    public function getSavingsByUserId($user_id) {
        $this->db->query('SELECT * FROM savings WHERE user_id = :user_id');
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();

        if ($this->db->rowCount() > 0) {
            return $row;
        } else {
            // No savings account exists, create one.
            $this->db->query('INSERT INTO savings (user_id, balance) VALUES (:user_id, 0.00)');
            $this->db->bind(':user_id', $user_id);
            if ($this->db->execute()) {
                // Now fetch the newly created account
                return $this->getSavingsByUserId($user_id);
            }
            return false; // Should not happen
        }
    }

    /**
     * Credits a specified amount to a user's savings account.
     * @param int $user_id The user's ID.
     * @param float $amount The amount to credit.
     * @return bool True on success, false on failure.
     */
    private function creditSavings($user_id, $amount) {
        $this->db->query('UPDATE savings SET balance = balance + :amount WHERE user_id = :user_id');
        $this->db->bind(':amount', $amount);
        $this->db->bind(':user_id', $user_id);

        return $this->db->execute();
    }

    /**
     * Moves a specified amount from the user's main wallet to their savings vault.
     * This acts like a transaction.
     * @param int $user_id The user's ID.
     * @param float $amount The amount to move.
     * @return bool True on success, false on failure.
     */
    public function moveToSavings($user_id, $amount) {
        // 1. Debit the main wallet first
        if ($this->userModel->debitWallet($user_id, $amount)) {
            // 2. If debit is successful, credit the savings account
            if ($this->creditSavings($user_id, $amount)) {
                return true; // Everything succeeded
            } else {
                // Critical failure: Debit succeeded but credit failed. Revert the debit.
                $this->userModel->creditWallet($user_id, $amount);
                return false;
            }
        } else {
            // Debit failed (e.g., insufficient funds)
            return false;
        }
    }
}