<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="stylesheet" href="/c2c-platform/assets/css/styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <h1><a href="/"><?= htmlspecialchars(SITE_NAME) ?></a></h1>
            </div>
            
            <nav class="main-nav">
                <ul class="nav-list">
                    <li><a href="/" class="nav-link">Home</a></li>
                    <li><a href="/?page=products" class="nav-link">Products</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="/logout.php" class="nav-link">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/?page=login" class="nav-link">Login</a></li>
                        <li><a href="/register.php" class="nav-link">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="header-actions">
            <button id="theme-toggle" class="theme-toggle">
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun" style="display: none;"></i>
            </button>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/cart.php" class="cart-icon" aria-label="Shopping cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Mobile menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <main class="main-content">  
        <div class="container">
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success_msg']) ?>
                    <?php unset($_SESSION['success_msg']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error_msg']) ?>
                    <?php unset($_SESSION['error_msg']); ?>
                </div>
            <?php endif; ?>