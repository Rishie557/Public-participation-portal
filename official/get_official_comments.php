<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || !in_array($_SESSION['role'], ['official', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access restricted.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['role'] === 'admin';

if (!$is_admin) {
    $verCheck = $conn->prepare("SELECT status FROM official_verifications WHERE user_id = ?");
    $verCheck->bind_param('i', $user_id);
    $verCheck->execute();
    $verRow = $verCheck->get_result()->fetch_assoc();
    $verCheck->close();

    if (!$verRow || $verRow['status'] !== 'approved') {
        http_response_code(403);
        echo json_encode(['error' => 'Your official registration is not yet approved.']);
        exit;
    }
}

$bill_slug = trim($_GET['bill_slug'] ?? '');
if (!$bill_slug) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid bill.']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT c.id, c.comment_text, c.created_at, c.status, u.full_name
     FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.bill_slug = ? AND c.status IN ('active', 'pending_deletion')
     ORDER BY c.created_at DESC
     LIMIT 200"
);
$stmt->bind_param('s', $bill_slug);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'id' => (int)$row['id'],
        'name' => $row['full_name'],
        'text' => $row['comment_text'],
        'time' => date('j M, H:i', strtotime($row['created_at'])),
        'status' => $row['status']
    ];
}
$stmt->close();
$conn->close();
echo json_encode($comments);