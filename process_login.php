<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    redirect('login.php', 'Invalid CSRF token', 'error');
}

// Sanitize inputs
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    redirect('login.php', 'Email and password are required', 'error');
}

global $conn;

try {
    $stmt = $conn->prepare("SELECT id, name, password, role_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        redirect('login.php', 'Invalid credentials', 'error');
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        redirect('login.php', 'Invalid credentials', 'error');
    }

    // Successful login: set session data
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $user['name'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['last_activity'] = time();

    // Redirect based on role
    switch ($user['role_id']) {
        case 1: // Admin
            redirect('admin/dashboard.php');
            break;
        case 2: // Staff
            redirect('staff/dashboard.php');
            break;
        case 3: // Customer
            redirect('dashboard.php');
            break;
        default:
            session_destroy();
            redirect('login.php', 'Invalid user role', 'error');
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    redirect('login.php', 'System error. Please try again.', 'error');
}
?>
