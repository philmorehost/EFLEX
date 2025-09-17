<?php
// src/models/User.php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Find a user by their ID.
     * @param int $id The user's ID.
     * @return mixed The user data as an associative array, or false if not found.
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Find a user by their email address.
     * @param string $email The user's email.
     * @return mixed The user data as an associative array, or false if not found.
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Create a new user in the database.
     * @param string $username The user's username.
     * @param string $email The user's email.
     * @param string $passwordHash The hashed password.
     * @param string $userType The user's type ('buyer' or 'seller').
     * @return bool True on success, false on failure.
     */
    public function create($username, $email, $passwordHash, $userType) {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)'
            );
            return $stmt->execute([$username, $email, $passwordHash, $userType]);
        } catch (PDOException $e) {
            // You might want to log this error in a real application
            return false;
        }
    }
}
