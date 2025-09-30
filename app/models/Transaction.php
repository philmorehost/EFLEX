<?php
namespace Models;

use Core\Database;

class Transaction {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Creates a new transaction record in the database.
     * @param array $data The transaction data.
     * @return int|false The ID of the new transaction or false on failure.
     */
    public function create($data) {
        $this->db->query('INSERT INTO transactions (user_id, service, transaction_ref, amount, status, description, metadata)
                         VALUES (:user_id, :service, :ref, :amount, :status, :desc, :meta)');

        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':service', $data['service']);
        $this->db->bind(':ref', $data['ref']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':status', $data['status'] ?? 'pending');
        $this->db->bind(':desc', $data['description']);
        $this->db->bind(':meta', $data['metadata'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Updates the status of an existing transaction.
     * @param int $id The ID of the transaction to update.
     * @param string $status The new status (e.g., 'successful', 'failed').
     * @param mixed $metadata Optional metadata to append or update.
     * @return bool True on success, false on failure.
     */
    public function updateStatus($id, $status, $metadata = null) {
        $this->db->query('UPDATE transactions SET status = :status, metadata = :metadata WHERE id = :id');

        $this->db->bind(':status', $status);
        $this->db->bind(':metadata', $metadata);
        $this->db->bind(':id', $id);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
}