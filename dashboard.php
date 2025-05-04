<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = "Dashboard";
include 'views/header.php';
?>

<div class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    
    <div class="user-type-badge">
        Account Type: <?= ucfirst($_SESSION['user_type']) ?>
    </div>
    
    <div class="dashboard-actions">
        <?php if ($_SESSION['user_type'] === 'seller'): ?>
            <a href="add_product.php" class="btn">Add New Product</a>
        <?php endif; ?>
        
        <a href="products.php" class="btn">Browse Products</a>
        <a href="logout.php" class="btn logout">Logout</a>
    </div>
</div>

<?php include 'views/footer.php'; ?>