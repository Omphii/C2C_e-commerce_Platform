<?php
require 'includes/config.php';
require 'includes/db_connect.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->query("SELECT 1");
    echo "<h1 style='color:green'>Database connection successful!</h1>";
    
    $products = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    echo "<p>Products in database: $products</p>";
} catch(PDOException $e) {
    echo "<h1 style='color:red'>Database error:</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>