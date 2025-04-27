<?php
require_once __DIR__ . '/config.php';

class Auth {
    // Role constants (must match your roles table)
    const ROLES = [
        1 => ['name' => 'admin', 'description' => 'Full system access'],
        2 => ['name' => 'staff', 'description' => 'Limited admin access'],
        3 => ['name' => 'customer', 'description' => 'Standard user']
    ];

    // Session timeout in seconds (30 minutes)
    const SESSION_TIMEOUT = 1800;

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function hasRole(int $required_role_id): bool {
        if (!self::isLoggedIn()) return false;
        
        // Admins bypass all role checks
        if ($_SESSION['role_id'] === 1) return true;
        
        return $_SESSION['role_id'] === $required_role_id;
    }

    public static function requireRole(int $required_role_id): void {
        if (!self::hasRole($required_role_id)) {
            self::logSecurityEvent(
                $_SESSION['user_id'] ?? null,
                "Unauthorized access attempt to role ID: $required_role_id"
            );

            if (!self::isLoggedIn()) {
                self::redirectToLogin();
            } else {
                redirect('dashboard.php', 'You don\'t have permission', 'error');
            }
        }
    }

    public static function login(int $user_id, string $email, string $name, int $role_id): void {
        // Validate role exists
        if (!array_key_exists($role_id, self::ROLES)) {
            throw new InvalidArgumentException("Invalid role ID: $role_id");
        }

        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        // Set session data
        $_SESSION = [
            'user_id' => $user_id,
            'email' => $email,
            'name' => $name,
            'role_id' => $role_id,
            'role_name' => self::ROLES[$role_id]['name'],
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'last_activity' => time()
        ];

        // Reset login attempts
        global $conn;
        $stmt = $conn->prepare("UPDATE users SET login_attempts = 0, last_login = NOW() WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        self::logSecurityEvent($user_id, "User logged in");
    }

    public static function logout(): void {
        if (self::isLoggedIn()) {
            self::logSecurityEvent($_SESSION['user_id'], "User logged out");
        }

        // Clear session data
        $_SESSION = [];

        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function checkSessionTimeout(): void {
        if (!self::isLoggedIn()) {
            return;
        }

        // Verify session consistency
        if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] || 
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            self::logout();
            self::redirectToLogin('Session security violation');
        }

        // Check timeout
        if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
            self::logout();
            self::redirectToLogin('Session expired');
        }

        // Update last activity
        $_SESSION['last_activity'] = time();
    }

    public static function redirectBasedOnRole(): void {
        if (!self::isLoggedIn()) {
            self::redirectToLogin();
        }

        switch ($_SESSION['role_id']) {
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
                self::logout();
                self::redirectToLogin('Invalid role configuration');
        }
    }

    private static function logSecurityEvent(?int $user_id, string $message): void {
        global $conn;
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $stmt = $conn->prepare("INSERT INTO security_logs (user_id, ip_address, user_agent, event) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $ip, $user_agent, $message);
        $stmt->execute();
    }

    public static function redirectToLogin(string $message = null): void {
        redirect('login.php', $message ?? 'Please login', 'error');
    }
}

// Initialize security checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (Auth::isLoggedIn()) {
    Auth::checkSessionTimeout();
    
    // Verify role still exists in system
    if (!array_key_exists($_SESSION['role_id'], Auth::ROLES)) {
        Auth::logout();
        Auth::redirectToLogin('Your account role is no longer valid');
    }
}
?>