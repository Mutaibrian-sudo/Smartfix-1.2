<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Verify CSRF first
verify_csrf_token();

// Get and sanitize inputs
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($email) || empty($password)) {
    Auth::redirectToLogin('Email and password are required');
}

try {
    // Get user from database
    $stmt = $conn->prepare("
        SELECT id, name, password, role_id 
        FROM users 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        Auth::redirectToLogin('Invalid credentials');
    }

    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['password'])) {
        Auth::login($user['id'], $email, $user['name'], $user['role_id']);
        Auth::redirectBasedOnRole();
    } else {
        Auth::redirectToLogin('Invalid credentials');
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    Auth::redirectToLogin('System error. Please try again.');
}
?>