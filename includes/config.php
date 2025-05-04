<?php
// Check if constants are already defined
if (!defined('DB_HOST')) {
    // Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'c2c_platform');
    define('DB_USER', 'root');
    define('DB_PASS', '');

    // Site Configuration
    define('SITE_NAME', 'South Africa C2C Marketplace');
    define('BASE_URL', 'http://localhost/c2c-platform');
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>