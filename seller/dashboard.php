<?php
require '../includes/config.php';
require '../includes/db_connect.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: /c2c-platform/login.php");
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get seller stats
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured_products
        FROM products 
        WHERE seller_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent orders
    $stmt = $conn->prepare("
        SELECT o.id, o.created_at, p.name, o.quantity, o.total_price 
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE p.seller_id = ?
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/c2c-platform/assets/css/styles.css">
</head>
<body>
    <?php include '../views/header.php'; ?>
    
    <div class="container">
        <h1>Seller Dashboard</h1>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Products</h3>
                <p><?= $stats['total_products'] ?? 0 ?></p>
            </div>
            <div class="stat-card">
                <h3>Featured Products</h3>
                <p><?= $stats['featured_products'] ?? 0 ?></p>
            </div>
        </div>
        
        <section class="recent-orders">
            <h2>Recent Orders</h2>
            <?php if (!empty($orders)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td><?= $order['quantity'] ?></td>
                            <td>R<?= number_format($order['total_price'], 2) ?></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent orders found.</p>
            <?php endif; ?>
        </section>
    </div>
    
    <?php include '../views/footer.php'; ?>
</body>
</html>