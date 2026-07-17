<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'official') {
    http_response_code(403);
    echo json_encode(['error' => 'Access restricted.']);
    exit;
}

$user_id = $_SESSION['user_id'];

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

$stmt = $conn->prepare(
    "SELECT id, change_type, bill_slug, payload, status, reviewed_at
     FROM pending_changes
     WHERE proposed_by = ?
     ORDER BY id DESC"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();

$proposals = [];
while ($row = $res->fetch_assoc()) {
    $row['payload'] = json_decode($row['payload'], true);
    $proposals[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode($proposals);