<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require 'config/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$full_name         = trim($data['full_name'] ?? '');
$phone             = trim($data['phone'] ?? '') ?: null;
$email             = trim($data['email'] ?? '');
$password          = $data['password'] ?? '';
$role              = ($data['role'] ?? 'citizen') === 'official' ? 'official' : 'citizen';
$county_name       = trim($data['county_name'] ?? '') ?: null;
$constituency_name = trim($data['constituency_name'] ?? '') ?: null;
$office_id         = trim($data['office_id_number'] ?? '') ?: null;
$national_id       = trim($data['national_id_number'] ?? '');
$position_title    = trim($data['position_title'] ?? '') ?: null;
$government_level  = ($data['government_level'] ?? '') ?: null;
$office_department = trim($data['office_department'] ?? '') ?: null;

if (!$full_name || !$email || !$national_id || strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Please fill in your name, email, national ID, and an 8+ character password']);
    exit;
}
if ($role === 'official' && (!$office_id || !$position_title || !$government_level || !$office_department)) {
    http_response_code(400);
    echo json_encode(['error' => 'Office ID, position/title, government level, and office/department are required for government official accounts']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM roles WHERE name = ?");
$stmt->bind_param('s', $role);
$stmt->execute();
$roleRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$roleRow) {
    http_response_code(500);
    echo json_encode(['error' => 'Role not configured on server']);
    exit;
}
$role_id = $roleRow['id'];
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$is_verified = ($role === 'citizen') ? 1 : 0;

$stmt = $conn->prepare(
    "INSERT INTO users (full_name, phone, email, password_hash, role_id, county_name, constituency_name, office_id_number, is_verified, national_id_number, position_title, government_level, office_department)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    'ssssissssisss',
    $full_name, $phone, $email, $password_hash, $role_id, $county_name, $constituency_name, $office_id, $is_verified, $national_id, $position_title, $government_level, $office_department
);

if (!$stmt->execute()) {
    if ($conn->errno === 1062) {
        http_response_code(409);
        echo json_encode(['error' => 'That phone number, email, or national ID is already registered']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Could not create account: ' . $conn->error]);
    }
    exit;
}

$user_id = $stmt->insert_id;
$stmt->close();

if ($role === 'official') {
    $stmt = $conn->prepare("INSERT INTO official_verifications (user_id, status) VALUES (?, 'pending')");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'message' => $role === 'official'
        ? 'Account created. An admin will verify your office ID before it is activated.'
        : 'Account created successfully.'
]);

$conn->close();