<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize session
session_start();

// Verify includes exist before requiring
function safe_require($path) {
    if (!file_exists($path)) {
        die("<h1>Missing Required File:</h1><pre>$path</pre>");
    }
    require $path;
}

// [Keep existing error reporting code]

// Load dependencies
require __DIR__.'/includes/config.php';
require __DIR__.'/includes/functions.php';
require __DIR__.'/includes/db_connect.php';

// Routing
$page = $_GET['page'] ?? 'home';
$allowed_pages = ['home', 'products', 'login', 'register'];
$page = in_array($page, $allowed_pages) ? $page : '404';
$products = getFeaturedProducts();

// Include header
include 'views/header.php';

// Show requested page
include "views/$page.php";

// Include footer
include 'views/footer.php';

// Simple routing
$page = $_GET['page'] ?? 'home';
$allowed_pages = ['home', 'products'];
$page = in_array($page, $allowed_pages) ? $page : '404';

?>