<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Verify authentication and role
if (!Auth::isLoggedIn()) {
    Auth::redirectToLogin();
}

// Redirect admins to admin dashboard
if ($_SESSION['role_id'] == 1) {
    redirect('admin/dashboard.php');
}

$user_id = $_SESSION['user_id'];

// Get user data
$user_stmt = $conn->prepare("
    SELECT u.*, r.name as role_name 
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.id = ?
");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get recent orders
$orders_stmt = $conn->prepare("
    SELECT o.id, s.name as service_name, o.total_price, o.status, o.created_at
    FROM orders o
    JOIN services s ON o.service_id = s.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$recent_orders = $orders_stmt->get_result();

// Get order stats
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending_payment' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders
    FROM orders
    WHERE user_id = ?
");
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | SmartFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-md-3">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <div class="col-md-9">
                <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
                <p class="text-muted"><?= ucfirst($user['role_name']) ?> Account</p>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Total Orders</h5>
                                <h3><?= $stats['total_orders'] ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <h3><?= $stats['pending_orders'] ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Completed</h5>
                                <h3><?= $stats['completed_orders'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $order['id'] ?></td>
                                        <td><?= htmlspecialchars($order['service_name']) ?></td>
                                        <td>KES <?= number_format($order['total_price'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $order['status'] == 'completed' ? 'success' : 
                                                ($order['status'] == 'pending_payment' ? 'warning' : 'info')
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-info">
                                You haven't placed any orders yet.
                            </div>
                            <a href="new_order.php" class="btn btn-primary">Place Your First Order</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>