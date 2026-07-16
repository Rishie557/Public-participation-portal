<?php
header('Content-Type: application/json');
require 'admin_session_check.php';
require 'config/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = intval($data['user_id'] ?? 0);
$notes = trim($data['notes'] ?? '') ?: null;
$dockets = $data['dockets'] ?? [];

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE official_verifications SET status = 'approved', reviewed_at = NOW(), notes = ? WHERE user_id = ?"
);
$stmt->bind_param('si', $notes, $user_id);
$stmt->execute();
$stmt->close();

// login.php checks users.is_verified directly, so approval must set it here too
$verifyStmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
$verifyStmt->bind_param('i', $user_id);
$verifyStmt->execute();
$verifyStmt->close();

if (!empty($dockets) && is_array($dockets)) {
    $insert = $conn->prepare("INSERT IGNORE INTO official_dockets (user_id, bill_slug) VALUES (?, ?)");
    foreach ($dockets as $slug) {
        $slug = trim($slug);
        if ($slug === '') continue;
        $insert->bind_param('is', $user_id, $slug);
        $insert->execute();
    }
    $insert->close();
}

echo json_encode(['success' => true]);
$conn->close();