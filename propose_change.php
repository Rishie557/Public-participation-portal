<?php
header('Content-Type: application/json');
session_start();
require 'config/db_connect.php';

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

$data = json_decode(file_get_contents('php://input'), true);
$change_type = $data['change_type'] ?? '';
$bill_slug = trim($data['bill_slug'] ?? '');
$payload = $data['payload'] ?? [];

if (!in_array($change_type, ['add_bill', 'remove_bill', 'edit_tax_spend'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid change type.']);
    exit;
}

// add_bill has no existing slug to check docket against — open to any approved official.
// remove_bill / edit_tax_spend require docket ownership of the existing bill.
if ($change_type !== 'add_bill') {
    if (!$bill_slug) {
        http_response_code(400);
        echo json_encode(['error' => 'bill_slug is required.']);
        exit;
    }
    $check = $conn->prepare("SELECT 1 FROM official_dockets WHERE user_id = ? AND bill_slug = ?");
    $check->bind_param('is', $user_id, $bill_slug);
    $check->execute();
    $owns = $check->get_result()->num_rows > 0;
    $check->close();

    if (!$owns) {
        http_response_code(403);
        echo json_encode(['error' => 'This bill is not in your docket.']);
        exit;
    }
}

if ($change_type === 'add_bill') {
    if (empty($payload['slug']) || empty($payload['title']) || empty($payload['bill_status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'slug, title, and bill_status are required to propose a new bill.']);
        exit;
    }
    $bill_slug = trim($payload['slug']);
}

$payload_json = json_encode($payload);

$stmt = $conn->prepare(
    "INSERT INTO pending_changes (change_type, bill_slug, payload, proposed_by)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('sssi', $change_type, $bill_slug, $payload_json, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'message' => 'Change submitted for admin review']);