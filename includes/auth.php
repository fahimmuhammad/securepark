<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(string $redirect = '/securepark/login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireAdmin(string $redirect = '/securepark/login.php'): void {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function getCurrentUser(): array {
    return [
        'id'             => $_SESSION['user_id']   ?? 0,
        'name'           => $_SESSION['name']       ?? '',
        'email'          => $_SESSION['email']      ?? '',
        'phone'          => $_SESSION['phone']      ?? '',
        'vehicle_number' => $_SESSION['vehicle_number'] ?? '',
        'role'           => $_SESSION['role']       ?? 'user',
    ];
}

function sanitize(mysqli $conn, mixed $value): string {
    return $conn->real_escape_string(htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8'));
}

function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function generateBookingRef(): string {
    return 'SPK-' . date('Y') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
}
