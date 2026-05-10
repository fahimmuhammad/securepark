<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /securepark/login.php');
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    $_SESSION['flash_type'] = 'error';
    $_SESSION['flash_msg']  = 'Email and password are required.';
    header('Location: /securepark/login.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, name, email, password, phone, vehicle_number, role FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['flash_type'] = 'error';
    $_SESSION['flash_msg']  = 'Invalid email or password. Please try again.';
    header('Location: /securepark/login.php');
    exit;
}

$_SESSION['user_id']        = $user['id'];
$_SESSION['name']           = $user['name'];
$_SESSION['email']          = $user['email'];
$_SESSION['phone']          = $user['phone'];
$_SESSION['vehicle_number'] = $user['vehicle_number'];
$_SESSION['role']           = $user['role'];

$redirect = $user['role'] === 'admin' ? '/securepark/admin/index.php' : '/securepark/dashboard.php';
header('Location: ' . $redirect);
exit;
