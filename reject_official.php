<?php
header('Content-Type: application/json');
require 'admin_session_check.php';
require 'config/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = intval($data['user_id'] ?? 0);
$notes = trim($data['notes'] ?? '') ?: null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE official_verifications SET status = 'rejected', reviewed_at = NOW(), notes = ? WHERE user_id = ?"
);
$stmt->bind_param('si', $notes, $user_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
$conn->close();