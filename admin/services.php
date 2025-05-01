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

// Handle service removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_service_id'])) {
    $remove_service_id = intval($_POST['remove_service_id']);
    if ($remove_service_id > 0) {
        $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param("i", $remove_service_id);
        if ($stmt->execute()) {
            $success = "Service removed successfully.";
        } else {
            $error = "Failed to remove service.";
        }
        $stmt->close();
    }
}

// Handle new service submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $base_price = floatval($_POST['base_price'] ?? 0);
    $turnaround_hours = intval($_POST['turnaround_hours'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Basic validation
    if (empty($name) || empty($slug) || $base_price <= 0) {
        $error = "Please fill in all required fields with valid values.";
    } else {
        // Insert new service
        $stmt = $conn->prepare("INSERT INTO services (name, slug, description, base_price, turnaround_hours, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdis", $name, $slug, $description, $base_price, $turnaround_hours, $is_active);
        if ($stmt->execute()) {
            $success = "Service added successfully.";
        } else {
            $error = "Failed to add service. Make sure the slug is unique.";
        }
        $stmt->close();
    }
}

// Fetch services
$result = $conn->query("SELECT id, name, slug, description, base_price, turnaround_hours, is_active, created_at FROM services ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Services | SmartFix Admin</title>
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
                <h2 class="h3 mb-4">Services</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Add Service Form -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="add_service" value="1" />
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="name" class="form-label">Service Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required />
                        </div>
                        <div class="col-md-4">
                            <label for="slug" class="form-label">Slug *</label>
                            <input type="text" class="form-control" id="slug" name="slug" required />
                        </div>
                        <div class="col-md-4">
                            <label for="base_price" class="form-label">Base Price (KES) *</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="base_price" name="base_price" required />
                        </div>
                        <div class="col-md-6">
                            <label for="turnaround_hours" class="form-label">Turnaround Hours</label>
                            <input type="number" min="0" class="form-control" id="turnaround_hours" name="turnaround_hours" />
                        </div>
                        <div class="col-md-6">
                            <label for="is_active" class="form-label d-block">Active</label>
                            <input type="checkbox" id="is_active" name="is_active" checked />
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Add Service</button>
                </form>

                <!-- Services List -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Base Price (KES)</th>
                                <th>Turnaround Hours</th>
                                <th>Active</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($service = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($service['name']) ?></td>
                                    <td><?= htmlspecialchars($service['slug']) ?></td>
                                    <td><?= number_format($service['base_price'], 2) ?></td>
                                    <td><?= htmlspecialchars($service['turnaround_hours']) ?></td>
                                    <td>
                                        <?php if ($service['is_active']): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($service['created_at']) ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to remove this service?');" style="display:inline;">
                                            <input type="hidden" name="remove_service_id" value="<?= $service['id'] ?>" />
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
