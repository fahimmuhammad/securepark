<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /securepark/login.php');
    exit;
}

$name             = trim($_POST['name']             ?? '');
$email            = trim($_POST['email']            ?? '');
$phone            = trim($_POST['phone']            ?? '');
$vehicle_number   = trim($_POST['vehicle_number']   ?? '');
$password         = $_POST['password']              ?? '';
$confirm_password = $_POST['confirm_password']      ?? '';

if (!$name || !$email || !$password) {
    $_SESSION['flash_type'] = 'error';
    $_SESSION['flash_msg']  = 'Name, email and password are required.';
    header('Location: /securepark/login.php?tab=register');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_type'] = 'error';
    $_SESSION['flash_msg']  = 'Please enter a valid email address.';
    header('Location: /securepark/login.php?tab=register');
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['flash_type'] = 'error';
    $_SESSION['flash_msg']  = 'Password must be at least 8 characters.';
    header('Location: /securepark/login.php?tab=register');
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['flash_type'] = 'error';
    $_SESSION['flash_msg']  = 'Passwords do not match.';
    header('Location: /securepark/login.php?tab=register');
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $_SESSION['flash_type'] = 'error';
    $_SESSION['flash_msg']  = 'An account with this email already exists.';
    header('Location: /securepark/login.php?tab=register');
    exit;
}
$stmt->close();

$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt   = $conn->prepare("INSERT INTO users (name, email, password, phone, vehicle_number, role) VALUES (?, ?, ?, ?, ?, 'user')");
$stmt->bind_param('sssss', $name, $email, $hashed, $phone, $vehicle_number);

if (!$stmt->execute()) {
    $stmt->close();
    $_SESSION['flash_type'] = 'error';
    $_SESSION['flash_msg']  = 'Registration failed. Please try again.';
    header('Location: /securepark/login.php?tab=register');
    exit;
}

$new_id = $stmt->insert_id;
$stmt->close();

$_SESSION['user_id']        = $new_id;
$_SESSION['name']           = $name;
$_SESSION['email']          = $email;
$_SESSION['phone']          = $phone;
$_SESSION['vehicle_number'] = $vehicle_number;
$_SESSION['role']           = 'user';
$_SESSION['flash_type']     = 'success';
$_SESSION['flash_msg']      = 'Account created successfully! Welcome to SecurePark.';

header('Location: /securepark/dashboard.php');
exit;
