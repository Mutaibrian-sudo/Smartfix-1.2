<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    // Define roles (should match your database)
    const ROLES = [
        1 => ['name' => 'admin', 'description' => 'Full system access'],
        2 => ['name' => 'staff', 'description' => 'Limited admin access'],
        3 => ['name' => 'customer', 'description' => 'Standard user'],
    ];

    // Session timeout in seconds (30 minutes)
    const SESSION_TIMEOUT = 1800;

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function login(int $user_id, string $email, string $name, int $role_id): void {
        global $conn;

        session_regenerate_id(true); // prevent session fixation

        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['role_id'] = $role_id;
        $_SESSION['role_name'] = self::ROLES[$role_id]['name'] ?? 'unknown';
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['last_activity'] = time();

        // Reset login attempts
        $stmt = $conn->prepare("UPDATE users SET login_attempts = 0, last_login = NOW() WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }

        self::logSecurityEvent($user_id, "User logged in");
    }

    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (self::isLoggedIn()) {
            self::logSecurityEvent($_SESSION['user_id'], "User logged out");
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, 
                $params["path"], 
                $params["domain"], 
                $params["secure"], 
                $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function redirectToLogin(string $message = 'Please login'): void {
        redirect('login.php', $message, 'error');
        exit;
    }

    public static function redirectBasedOnRole(): void {
        if (!self::isLoggedIn()) {
            self::redirectToLogin();
        }

        switch ($_SESSION['role_id']) {
            case 1:
                redirect('admin/dashboard.php');
                break;
            case 2:
                redirect('staff/dashboard.php');
                break;
            case 3:
                redirect('dashboard.php');
                break;
            default:
                self::logout();
                self::redirectToLogin('Invalid role configuration.');
        }
        exit;
    }

    public static function hasRole(int $required_role_id): bool {
        if (!self::isLoggedIn()) {
            return false;
        }

        // Admins can access everything
        if ($_SESSION['role_id'] === 1) {
            return true;
        }

        return $_SESSION['role_id'] === $required_role_id;
    }

    public static function requireRole(int $required_role_id): void {
        if (!self::hasRole($required_role_id)) {
            self::logSecurityEvent($_SESSION['user_id'] ?? null, "Unauthorized access attempt to role ID $required_role_id");

            if (!self::isLoggedIn()) {
                self::redirectToLogin();
            } else {
                redirect('dashboard.php', 'You don\'t have permission to access this area.', 'error');
                exit;
            }
        }
    }

    public static function checkSessionTimeout(): void {
        if (!self::isLoggedIn()) {
            return;
        }

        if (
            ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) || 
            ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'])
        ) {
            self::logout();
            self::redirectToLogin('Session security violation.');
        }

        if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
            self::logout();
            self::redirectToLogin('Session expired. Please login again.');
        }

        $_SESSION['last_activity'] = time();
    }

    private static function logSecurityEvent(?int $user_id, string $message): void {
        global $conn;

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = $conn->prepare("INSERT INTO security_logs (user_id, ip_address, user_agent, event) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isss", $user_id, $ip, $user_agent, $message);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Initialize session timeout checker on every page load
if (Auth::isLoggedIn()) {
    Auth::checkSessionTimeout();

    // If role no longer exists
    if (!array_key_exists($_SESSION['role_id'], Auth::ROLES)) {
        Auth::logout();
        Auth::redirectToLogin('Your account role is no longer valid.');
    }
}
?>
