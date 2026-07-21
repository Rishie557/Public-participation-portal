<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';
require __DIR__ . '/../includes/docket_helpers.php';

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

$data = json_decode(file_get_contents('php://input'), true);
$comment_id = (int)($data['comment_id'] ?? 0);

if (!$comment_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid comment.']);
    exit;
}

$lookup = $conn->prepare("SELECT bill_slug, status, user_id FROM comments WHERE id = ?");
$lookup->bind_param('i', $comment_id);
$lookup->execute();
$row = $lookup->get_result()->fetch_assoc();
$lookup->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Comment not found.']);
    exit;
}

if ($row['status'] !== 'active') {
    http_response_code(409);
    echo json_encode(['error' => 'This comment is not active.']);
    exit;
}

if (!$is_admin && !officialOwnsBill($conn, $user_id, $row['bill_slug'])) {
    http_response_code(403);
    echo json_encode(['error' => 'This bill is not in your docket.']);
    exit;
}

if (empty($row['user_id'])) {
    echo json_encode(['success' => true, 'notified' => false]);
    exit;
}

$message = "An official from the docket for \"{$row['bill_slug']}\" found your comment useful. Thank you for your input.";

$notif = $conn->prepare(
    "INSERT IGNORE INTO notifications (user_id, comment_id, type, message, bill_slug)
     VALUES (?, ?, 'comment_appreciated', ?, ?)"
);
$notif->bind_param('iiss', $row['user_id'], $comment_id, $message, $row['bill_slug']);
$notif->execute();
$wasInserted = $notif->affected_rows > 0;
$notif->close();
$conn->close();

if (!$wasInserted) {
    echo json_encode(['success' => true, 'notified' => false, 'already_marked' => true]);
    exit;
}

echo json_encode(['success' => true, 'notified' => true]);