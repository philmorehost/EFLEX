<?php
namespace Models;

use Core\Database;

class LandingPage {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Gets all landing page content from the database.
     * @return array An associative array of content_key => content_value.
     */
    public function getContent() {
        $this->db->query('SELECT content_key, content_value FROM landing_page_content');
        $results = $this->db->resultSet();

        $contentArray = [];
        foreach ($results as $row) {
            $contentArray[$row->content_key] = $row->content_value;
        }
        return $contentArray;
    }

    /**
     * Updates a specific piece of landing page content.
     * @param string $key The content_key to update.
     * @param string $value The new content_value.
     * @return bool True on success, false on failure.
     */
    public function updateContent($key, $value) {
        $this->db->query('UPDATE landing_page_content SET content_value = :value WHERE content_key = :key');
        $this->db->bind(':value', $value);
        $this->db->bind(':key', $key);

        return $this->db->execute();
    }
}