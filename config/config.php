<?php
// Suppress GD module warning (common XAMPP issue)
error_reporting(E_ALL & ~E_WARNING);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'weldios_portal');

// Application configuration
define('APP_NAME', 'weldios university Verification Portal');

// Dynamic BASE_URL detection
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    
    // Remove known subdirectories from the path to find the true base
    $path = str_replace(['/config', '/admin'], '', $path);
    
    // Ensure path ends with a single slash
    $path = rtrim($path, '/') . '/';
    
    return $protocol . $host . $path;
}

define('BASE_URL', getBaseUrl());
define('ADMIN_URL', BASE_URL . 'admin/');

// Connect to database
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Start session
session_start();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Utility functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateCertificateNumber() {
    return 'WLD/' . date('Y') . '/' . str_pad(rand(1, 9999), 3, '0', STR_PAD_LEFT);
}

function generateProfileUrl() {
    return 'profile_' . uniqid();
}

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirectToLogin() {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit();
}
?>