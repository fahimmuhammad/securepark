<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'securepark_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(503);
    die(json_encode(['error' => 'Database connection failed. Please run setup.php first.']));
}

$conn->set_charset('utf8mb4');
