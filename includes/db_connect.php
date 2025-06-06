﻿<?php
require 'config.php';

class Database {
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME, 
                DB_USER, 
                DB_PASS
            );
            // CORRECTED LINE - removed extra _EXCEPTION
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>