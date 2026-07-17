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
$change_id = (int)($data['change_id'] ?? 0);
$action = $data['action'] ?? '';

if (!$change_id || !in_array($action, ['approve', 'reject'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'change_id and valid action are required']);
    exit;
}

$lookup = $conn->prepare("SELECT * FROM pending_changes WHERE id = ? AND status = 'pending'");
$lookup->bind_param('i', $change_id);
$lookup->execute();
$change = $lookup->get_result()->fetch_assoc();
$lookup->close();

if (!$change) {
    http_response_code(404);
    echo json_encode(['error' => 'Change not found or already reviewed.']);
    exit;
}

if ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE pending_changes SET status = 'rejected', reviewed_at = NOW() WHERE id = ?");
    $stmt->bind_param('i', $change_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'status' => 'rejected']);
    exit;
}

// approve: actually apply the change
$payload = json_decode($change['payload'], true);

if ($change['change_type'] === 'add_bill') {
    $stmt = $conn->prepare(
        "INSERT INTO bills (slug, title, bill_status, group_label) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param('ssss', $payload['slug'], $payload['title'], $payload['bill_status'], $payload['group_label']);
    $stmt->execute();
    $stmt->close();

} elseif ($change['change_type'] === 'remove_bill') {
    $stmt = $conn->prepare("UPDATE bills SET status = 'archived' WHERE slug = ?");
    $stmt->bind_param('s', $change['bill_slug']);
    $stmt->execute();
    $stmt->close();

} elseif ($change['change_type'] === 'edit_tax_spend') {
    $year = (int)$payload['year'];
    $amount = (float)$payload['amount'];
    $notes = $payload['notes'] ?? null;
    $stmt = $conn->prepare(
        "INSERT INTO bill_tax_spend (bill_slug, year, amount, notes)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE amount = VALUES(amount), notes = VALUES(notes)"
    );
    $stmt->bind_param('sids', $change['bill_slug'], $year, $amount, $notes);
    $stmt->execute();
    $stmt->close();
}

$update = $conn->prepare("UPDATE pending_changes SET status = 'approved', reviewed_at = NOW() WHERE id = ?");
$update->bind_param('i', $change_id);
$update->execute();
$update->close();
$conn->close();

echo json_encode(['success' => true, 'status' => 'approved']);