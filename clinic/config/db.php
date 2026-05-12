<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'MentalHealthDB');

class Database {
    private static ?Database $instance = null;
    private mysqli $connection;

    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->connection->connect_error) {
            die('Database connection failed: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public static function getConnection(): mysqli {
        return self::getInstance()->connection;
    }
}