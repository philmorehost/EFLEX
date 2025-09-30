<?php
namespace Models;

use Core\Database;

class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Register user
    public function register($data) {
        $this->db->query('INSERT INTO users (username, email, password, first_name, last_name, phone) VALUES(:username, :email, :password, :first_name, :last_name, :phone)');
        // Bind values
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone', $data['phone']);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Login User
    public function login($email, $password) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if ($row) {
            $hashed_password = $row->password;
            if (password_verify($password, $hashed_password)) {
                return $row;
            }
        }

        return false;
    }

    // Find user by email
    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Check row
        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Find user by username
    public function findUserByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);

        $row = $this->db->single();

        // Check row
        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Get User by ID
    public function getUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);

        $row = $this->db->single();

        return $row;
    }

    /**
     * Debits a specified amount from a user's wallet.
     * @param int $user_id The user's ID.
     * @param float $amount The amount to debit.
     * @return bool True on success, false on failure.
     */
    public function debitWallet($user_id, $amount) {
        $this->db->query('UPDATE users SET wallet_balance = wallet_balance - :amount WHERE id = :user_id AND wallet_balance >= :amount');
        $this->db->bind(':amount', $amount);
        $this->db->bind(':user_id', $user_id);

        if ($this->db->execute()) {
            // Check if any row was actually updated
            return $this->db->rowCount() > 0;
        }
        return false;
    }

    /**
     * Credits a specified amount to a user's wallet.
     * @param int $user_id The user's ID.
     * @param float $amount The amount to credit.
     * @return bool True on success, false on failure.
     */
    public function creditWallet($user_id, $amount) {
        $this->db->query('UPDATE users SET wallet_balance = wallet_balance + :amount WHERE id = :user_id');
        $this->db->bind(':amount', $amount);
        $this->db->bind(':user_id', $user_id);

        return $this->db->execute();
    }
}