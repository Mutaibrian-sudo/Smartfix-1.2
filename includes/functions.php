<?php
// =============================================
// REDIRECT WITH FLASH MESSAGES
// =============================================
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }
    header("Location: " . BASE_URL . $url);
    exit();
}

// =============================================
// SECURE INPUT SANITIZATION
// =============================================
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// =============================================
// PASSWORD HASHING (with cost factor)
// =============================================
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// =============================================
// M-PESA PAYMENT HANDLER (Simulated)
// =============================================
function mpesa_payment($phone, $amount, $order_id) {
    // In production, use Safaricom Daraja API:
    // https://developer.safaricom.co.ke/docs
    
    // Mock implementation for testing
    if (!preg_match('/^254[17]\d{8}$/', $phone)) {
        return ['success' => false, 'error' => 'Invalid Kenyan phone'];
    }

    return [
        'success' => true,
        'transaction_code' => 'MP' . time() . rand(100, 999)
    ];
}

// =============================================
// LOGIN ATTEMPT TRACKING (Anti-Brute Force)
// =============================================
function handle_failed_login($email) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE users SET 
        login_attempts = login_attempts + 1,
        last_attempt = NOW()
        WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Check if should lock account
    $result = $conn->query("SELECT login_attempts FROM users WHERE email = '$email'");
    $user = $result->fetch_assoc();
    
    if ($user['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['error'] = "Account locked for " . (LOGIN_LOCKOUT_TIME/60) . " minutes";
        return false;
    }
    return true;
}

// =============================================
// FILE UPLOAD VALIDATION
// =============================================
function validate_upload($file, $allowed_types = ['pdf', 'docx', 'jpg']) {
    $max_size = 5 * 1024 * 1024; // 5MB
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large (max 5MB)'];
    }

    return ['success' => true];
}

// =============================================
// CSRF TOKEN VERIFICATION
// =============================================
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || 
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed!");
    }
}
?>