<?php
header('Content-Type: application/json');
session_start();
require 'db_connect.php';

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$comment_id = (int)($data['comment_id'] ?? 0);
$action = $data['action'] ?? '';

if (!$comment_id || !in_array($action, ['approve', 'reject'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'comment_id and valid action are required']);
    exit;
}

if ($action === 'approve') {
    $stmt = $conn->prepare(
        "UPDATE comments
         SET status = 'deleted', reviewed_at = NOW()
         WHERE id = ? AND status = 'pending_deletion'"
    );
    $stmt->bind_param('i', $comment_id);
} else {
    $stmt = $conn->prepare(
        "UPDATE comments
         SET status = 'active', reviewed_at = NOW(), flagged_by = NULL, flagged_reason = NULL, flagged_at = NULL
         WHERE id = ? AND status = 'pending_deletion'"
    );
    $stmt->bind_param('i', $comment_id);
}
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Comment not found or not pending review']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();
$conn->close();
$new_status = $action === 'approve' ? 'deleted' : 'active';
echo json_encode(['success' => true, 'status' => $new_status]);