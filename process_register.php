<?php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords don’t match!";
        redirect('register.php');
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email already registered!";
        redirect('register.php');
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role_id) VALUES (?, ?, ?, ?, 2)");
    $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Account created! Log in now.";
        redirect('login.php');
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        redirect('register.php');
    }
}
?>