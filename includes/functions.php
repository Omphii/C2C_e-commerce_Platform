<?php
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return (isLoggedIn() && $_SESSION['user_type'] === 'admin');
}

function getFeaturedProducts($limit = 6) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT p.*, u.username as seller_name 
            FROM products p
            JOIN users u ON p.seller_id = u.id
            WHERE p.featured = 1 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function getSellerName($seller_id) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$seller_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['username'] : 'Unknown Seller';
    } catch(PDOException $e) {
        error_log("Seller lookup failed: " . $e->getMessage());
        return 'Seller Unknown';
    }
}

function getProductDetails($product_id) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT p.*, u.username as seller_name 
            FROM products p
            JOIN users u ON p.seller_id = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$product_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Product details error: " . $e->getMessage());
        return false;
    }
}

function getProductImagePath($imageName) {
    $imagePath = "/assets/images/products/" . htmlspecialchars($imageName);
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
    
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        return "/c2c-platform/assets/images/placeholder.jpg";
    }
    
    return $imagePath;
}

function calculateDiscount($original, $current) {
    if ($original <= 0 || $current >= $original) return 0;
    $discount = (($original - $current) / $original) * 100;
    return round($discount);
  }
?>