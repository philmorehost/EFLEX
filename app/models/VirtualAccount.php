<?php
namespace Models;

use Core\Database;

class VirtualAccount {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Get a user's virtual account details by their user ID.
     * @param int $user_id The ID of the user.
     * @return object|false The account object or false if not found.
     */
    public function getAccountByUserId($user_id) {
        $this->db->query('SELECT * FROM virtual_accounts WHERE user_id = :user_id AND is_active = 1');
        $this->db->bind(':user_id', $user_id);

        $row = $this->db->single();

        return $row;
    }

    /**
     * Create a new virtual account entry in the database.
     * @param array $data The account data from the API.
     * @return bool True on success, false on failure.
     */
    public function createVirtualAccount($data) {
        $this->db->query('INSERT INTO virtual_accounts (user_id, tracking_ref, account_number, account_name, bank_name, bank_code)
                         VALUES (:user_id, :tracking_ref, :account_number, :account_name, :bank_name, :bank_code)');

        // Bind values
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':tracking_ref', $data['tracking_ref']);
        $this->db->bind(':account_number', $data['account_number']);
        $this->db->bind(':account_name', $data['account_name']);
        $this->db->bind(':bank_name', $data['bank_name']);
        $this->db->bind(':bank_code', $data['bank_code']);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
}