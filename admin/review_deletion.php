<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

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

// Fetch details up front — needed either way, and required for the
// notification if this gets approved (deleted).
$lookup = $conn->prepare(
    "SELECT bill_slug, user_id, flagged_reason FROM comments WHERE id = ? AND status = 'pending_deletion'"
);
$lookup->bind_param('i', $comment_id);
$lookup->execute();
$row = $lookup->get_result()->fetch_assoc();
$lookup->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Comment not found or not pending review']);
    $conn->close();
    exit;
}

if ($action === 'approve') {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare(
            "UPDATE comments
             SET status = 'deleted', reviewed_at = NOW()
             WHERE id = ? AND status = 'pending_deletion'"
        );
        $stmt->bind_param('i', $comment_id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            // Someone else already reviewed it between our SELECT and UPDATE
            $stmt->close();
            $conn->rollback();
            http_response_code(409);
            echo json_encode(['error' => 'Comment was already reviewed.']);
            $conn->close();
            exit;
        }
        $stmt->close();

        if (!empty($row['user_id'])) {
            $reason = $row['flagged_reason'] ?: 'This comment did not meet our community guidelines.';
            $message = "Your comment on \"{$row['bill_slug']}\" was removed. Reason: {$reason}";
            $notif = $conn->prepare(
                "INSERT INTO notifications (user_id, comment_id, type, message, bill_slug)
                 VALUES (?, ?, 'comment_deleted', ?, ?)"
            );
            $notif->bind_param('iiss', $row['user_id'], $comment_id, $message, $row['bill_slug']);
            $notif->execute();
            $notif->close();
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Could not delete comment.']);
        $conn->close();
        exit;
    }

    $conn->close();
    echo json_encode(['success' => true, 'status' => 'deleted']);
    exit;
}

// action === 'reject': restore to active, no notification (nothing happened
// to the comment from the author's point of view)
$stmt = $conn->prepare(
    "UPDATE comments
     SET status = 'active', reviewed_at = NOW(), flagged_by = NULL, flagged_reason = NULL, flagged_at = NULL
     WHERE id = ? AND status = 'pending_deletion'"
);
$stmt->bind_param('i', $comment_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Comment was already reviewed.']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();
$conn->close();
echo json_encode(['success' => true, 'status' => 'active']);