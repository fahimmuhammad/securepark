<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();
header('Content-Type: application/json');

$uid             = (int)$_SESSION['user_id'];
$change_password = !empty($_POST['change_password']);

if ($change_password) {
    $current  = $_POST['current_password']  ?? '';
    $new_pass = $_POST['new_password']       ?? '';
    $confirm  = $_POST['confirm_password']   ?? '';

    if (!$current || !$new_pass || !$confirm) {
        echo json_encode(['error' => 'All password fields are required.']); exit;
    }
    if (strlen($new_pass) < 8) {
        echo json_encode(['error' => 'New password must be at least 8 characters.']); exit;
    }
    if ($new_pass !== $confirm) {
        echo json_encode(['error' => 'New passwords do not match.']); exit;
    }

    $row = $conn->query("SELECT password FROM users WHERE id = $uid")->fetch_assoc();
    if (!password_verify($current, $row['password'])) {
        echo json_encode(['error' => 'Current password is incorrect.']); exit;
    }

    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $stmt   = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $hashed, $uid);
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    } else {
        $stmt->close();
        echo json_encode(['error' => 'Failed to update password.']);
    }
} else {
    $name           = trim($_POST['name']           ?? '');
    $phone          = trim($_POST['phone']          ?? '');
    $vehicle_number = trim($_POST['vehicle_number'] ?? '');

    if (!$name) { echo json_encode(['error' => 'Name is required.']); exit; }

    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, vehicle_number = ? WHERE id = ?");
    $stmt->bind_param('sssi', $name, $phone, $vehicle_number, $uid);
    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['name']           = $name;
        $_SESSION['phone']          = $phone;
        $_SESSION['vehicle_number'] = $vehicle_number;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        $stmt->close();
        echo json_encode(['error' => 'Failed to update profile.']);
    }
}
