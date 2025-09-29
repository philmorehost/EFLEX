<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Database Class
 * Connects to the database using PDO, creates prepared statements,
 * binds values, and returns rows and results.
 */
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database handler
    private $stmt; // Statement
    private $error;

    public function __construct() {
        // Set DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true, // Persistent connection
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // Default fetch mode to object
            PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
        ];

        // Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // In a real app, you'd log this error, not display it
            die('Database Connection Error: ' . $this->error);
        }
    }

    /**
     * Prepare statement with query
     * @param string $sql The SQL query
     */
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    /**
     * Bind values to the prepared statement using named parameters
     * @param string $param The parameter placeholder (e.g., :email)
     * @param mixed $value The value to bind
     * @param int|null $type The PDO::PARAM_* constant
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Execute the prepared statement
     * @return bool True on success, false on failure
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Log this error
            error_log('Query Execution Failed: ' . $this->error);
            return false;
        }
    }

    /**
     * Get result set as array of objects
     * @return array
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Get single record as object
     * @return object
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Get row count
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Returns the last inserted ID
     * @return string
     */
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}