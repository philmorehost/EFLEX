<?php
namespace Models;

use Core\Database;

class Setting {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Get all settings from the database and return as an associative array.
     * @return array
     */
    public function getSettings() {
        $this->db->query('SELECT name, value FROM settings');
        $results = $this->db->resultSet();

        $settingsArray = [];
        foreach ($results as $row) {
            $settingsArray[$row->name] = $row->value;
        }
        return $settingsArray;
    }

    /**
     * Update a specific setting in the database.
     * @param string $name The name of the setting to update.
     * @param string $value The new value for the setting.
     * @return bool True on success, false on failure.
     */
    public function updateSetting($name, $value) {
        $this->db->query('UPDATE settings SET value = :value WHERE name = :name');
        $this->db->bind(':value', $value);
        $this->db->bind(':name', $name);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
}