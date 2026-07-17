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

$data = json_decode(file_get_contents('php://input'), true);
$comment_id = (int)($data['comment_id'] ?? 0);
$action = $data['action'] ?? '';
$reason = trim($data['reason'] ?? '');

if (!$comment_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid comment.']);
    exit;
}

$lookup = $conn->prepare("SELECT bill_slug, status FROM comments WHERE id = ?");
$lookup->bind_param('i', $comment_id);
$lookup->execute();
$row = $lookup->get_result()->fetch_assoc();
$lookup->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Comment not found.']);
    exit;
}

if (!$is_admin) {
    $check = $conn->prepare("SELECT 1 FROM official_dockets WHERE user_id = ? AND bill_slug = ?");
    $check->bind_param('is', $user_id, $row['bill_slug']);
    $check->execute();
    $owns = $check->get_result()->num_rows > 0;
    $check->close();

    if (!$owns) {
        http_response_code(403);
        echo json_encode(['error' => 'This bill is not in your docket.']);
        exit;
    }
}

if ($is_admin) {
    // Admins act immediately, no review needed
    $new_status = $action === 'unhide' ? 'active' : 'deleted';
    $stmt = $conn->prepare("UPDATE comments SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $new_status, $comment_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'status' => $new_status]);
    exit;
}

// Official: flagging only, requires reason, goes to pending review
if ($action !== 'flag') {
    http_response_code(400);
    echo json_encode(['error' => 'Officials must flag comments for admin review.']);
    exit;
}

if ($reason === '') {
    http_response_code(400);
    echo json_encode(['error' => 'A reason is required to flag a comment.']);
    exit;
}

if ($row['status'] !== 'active') {
    http_response_code(409);
    echo json_encode(['error' => 'This comment is already pending review or has been removed.']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE comments SET status = 'pending_deletion', flagged_by = ?, flagged_reason = ?, flagged_at = NOW() WHERE id = ?"
);
$stmt->bind_param('isi', $user_id, $reason, $comment_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'status' => 'pending_deletion']);