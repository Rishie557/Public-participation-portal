<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}

$sql = "SELECT c.id, c.comment_text, c.created_at, c.bill_slug,
               c.flagged_reason, c.flagged_at,
               u.full_name AS commenter_name,
               f.full_name AS flagged_by_name
        FROM comments c
        JOIN users u ON u.id = c.user_id
        JOIN users f ON f.id = c.flagged_by
        WHERE c.status = 'pending_deletion'
        ORDER BY c.flagged_at ASC";

$result = $conn->query($sql);

$pending = [];
while ($row = $result->fetch_assoc()) {
    $pending[] = [
        'id' => (int)$row['id'],
        'text' => $row['comment_text'],
        'bill_slug' => $row['bill_slug'],
        'commenter_name' => $row['commenter_name'],
        'flagged_by_name' => $row['flagged_by_name'],
        'flagged_reason' => $row['flagged_reason'],
        'flagged_at' => date('j M, H:i', strtotime($row['flagged_at']))
    ];
}
$conn->close();

echo json_encode($pending);