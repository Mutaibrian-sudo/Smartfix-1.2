<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

global $conn;

// Verify login first
if (!Auth::isLoggedIn()) {
    redirect('../login.php', 'Please login first', 'error');
}

// Verify admin access (role_id = 1)
if ($_SESSION['role_id'] != 1) {
    redirect('../dashboard.php', 'Unauthorized access', 'error');
}

$settings_file = __DIR__ . '/../config/settings.json';
$settings = json_decode(file_get_contents($settings_file), true);

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $theme = $_POST['theme'] ?? 'light';

    if (empty($site_name) || empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid site name and admin email.";
    } else {
        $settings['site_name'] = $site_name;
        $settings['admin_email'] = $admin_email;
        $settings['theme'] = in_array($theme, ['light', 'dark']) ? $theme : 'light';

        if (file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT))) {
            $success = "Settings updated successfully.";
        } else {
            $error = "Failed to save settings.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= htmlspecialchars($settings['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Settings | SmartFix Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/dashboard.css" />
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tools me-2"></i>SmartFix Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="themeToggle">
                            <i class="fas fa-moon"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($_SESSION['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 class="h3 mb-4">Settings</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required />
                    </div>
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Admin Email</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>" required />
                    </div>
                    <div class="mb-3">
                        <label for="theme" class="form-label">Theme</label>
                        <select class="form-select" id="theme" name="theme">
                            <option value="light" <?= (isset($settings['theme']) && $settings['theme'] === 'light') ? 'selected' : '' ?>>Light</option>
                            <option value="dark" <?= (isset($settings['theme']) && $settings['theme'] === 'dark') ? 'selected' : '' ?>>Dark</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
