<?php 
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Verify login first
if (!Auth::isLoggedIn()) {
    redirect('login.php', 'Please login first', 'error');
}

// Verify admin access (role_id = 1)
if ($_SESSION['role_id'] != Auth::ROLE_ADMIN) {
    redirect('dashboard.php', 'Unauthorized access', 'error');
}

// Fetch stats
$stats = [
    'total_orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0],
    'pending_orders' => $conn->query("SELECT COUNT(*) FROM orders WHERE status='pending_payment'")->fetch_row()[0],
    'completed_orders' => $conn->query("SELECT COUNT(*) FROM orders WHERE status='completed'")->fetch_row()[0],
    'revenue' => $conn->query("SELECT SUM(total_price) FROM orders WHERE status='completed'")->fetch_row()[0] ?? 0
];

// Recent orders (last 5)
$recent_orders = $conn->query("
    SELECT o.id, s.name as service, u.name as customer, o.total_price, o.status 
    FROM orders o
    JOIN services s ON o.service_id = s.id
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SmartFix</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tools me-2"></i>SmartFix Admin
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Nav Links -->
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
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 class="h3 mb-4">Dashboard Overview</h2>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Orders</h5>
                                <h2><?= htmlspecialchars($stats['total_orders']) ?></h2>
                                <i class="fas fa-shopping-cart stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <h2><?= htmlspecialchars($stats['pending_orders']) ?></h2>
                                <i class="fas fa-clock stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Completed</h5>
                                <h2><?= htmlspecialchars($stats['completed_orders']) ?></h2>
                                <i class="fas fa-check-circle stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Revenue</h5>
                                <h2>KES <?= number_format($stats['revenue']) ?></h2>
                                <i class="fas fa-coins stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($order['id']) ?></td>
                                        <td><?= htmlspecialchars($order['service']) ?></td>
                                        <td><?= htmlspecialchars($order['customer']) ?></td>
                                        <td>KES <?= number_format($order['total_price']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $order['status'] == 'completed' ? 'success' : 
                                                ($order['status'] == 'pending_payment' ? 'warning' : 'primary') 
                                            ?>">
                                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status']))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/dashboard.js"></script>
</body>
</html>