<?php
class Database {
    private static $instance = null; // Singleton instance
    private $connection;

    private function __construct() {
        $this->connection = new mysqli(
            'localhost',     // Host
            'root',          // Username 
            'root',              // Password
            'doctor_appointment_db'   // Database name
        );

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
?>