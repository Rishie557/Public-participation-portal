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

$stmt = $conn->prepare(
    "SELECT id, type, message, bill_slug, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 50"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
$unread_count = 0;
while ($row = $result->fetch_assoc()) {
    $row['is_read'] = (bool)$row['is_read'];
    if (!$row['is_read']) $unread_count++;
    $notifications[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unread_count,
]);