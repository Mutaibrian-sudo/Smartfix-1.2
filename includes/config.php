<?php
// =============================================
// DATABASE CONFIGURATION
// =============================================
$host = 'localhost';
$user = 'root';          // Change in production!
$pass = '';              // Change in production!
$db   = 'smartfix-1.2';

// Establish connection
try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to prevent SQL injection
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log("DB Error: " . $e->getMessage());
    die("System under maintenance. Please try later.");
}

// =============================================
// SESSION SECURITY
// =============================================
$cookie_secure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $cookie_secure = true;
}

session_start([
    'name' => 'SmartFixSession',
    'cookie_lifetime' => 86400, // 1 day
    'cookie_secure' => $cookie_secure,    // HTTPS only if HTTPS is on
    'cookie_httponly' => true,  // No JS access
    'use_strict_mode' => true   // Prevents session fixation
]);

// Regenerate ID to prevent session hijacking
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// =============================================
// GLOBAL CONSTANTS
// =============================================
define('BASE_URL', 'http://localhost/smartfix-1.2/'); // Change for production
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15 * 60); // 15 minutes
?>