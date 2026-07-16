<?php
header('Content-Type: application/json');
session_start();
require 'config/db_connect.php';

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
$bill_slug = trim($data['bill_slug'] ?? '');
$response_text = trim($data['response_text'] ?? '');

if (!$bill_slug || mb_strlen($response_text) < 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Please write a longer response.']);
    exit;
}

if (!$is_admin) {
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

$stmt = $conn->prepare("INSERT INTO official_responses (bill_slug, user_id, response_text, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param('sis', $bill_slug, $user_id, $response_text);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not post response.']);
    exit;
}
$stmt->close();
$conn->close();
echo json_encode(['success' => true]);