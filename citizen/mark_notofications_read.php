<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Optional: mark a single notification read via {"id": 123}.
// Otherwise mark everything read for this user.
$id = isset($data['id']) ? (int)$data['id'] : null;

if ($id) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $id, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => true]);