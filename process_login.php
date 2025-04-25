<?php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Validate Inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required!";
        redirect('login.php');
    }

    // 2. Fetch User from DB
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Invalid email or password!";
        redirect('login.php');
    }

    $user = $result->fetch_assoc();

    // 3. Verify Password
    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Invalid email or password!";
        redirect('login.php');
    }

    // 4. Set Session Data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = ($user['role_id'] == 1) ? 'admin' : 'customer';

    // 5. Redirect Based on Role
    if ($_SESSION['role'] === 'admin') {
        redirect('admin/dashboard.php', "Welcome back, Admin!");
    } else {
        redirect('dashboard.php', "Login successful!");
    }
} else {
    // Block direct access
    redirect('login.php');
}
?>