<?php


$conn = new mysqli('localhost', 'root', '', 'sauti_db');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$conn->set_charset('utf8mb4');