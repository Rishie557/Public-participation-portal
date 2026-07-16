<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require 'config/db_connect.php';

$data          = json_decode(file_get_contents('php://input'), true);
$identifier    = trim($data['identifier'] ?? '');   // phone or email
$password      = $data['password'] ?? '';
$expected_role = ($data['role'] ?? 'citizen') === 'official' ? 'official' : 'citizen';

if (!$identifier || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter your phone/email and password']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT u.id, u.full_name, u.password_hash, u.is_verified, u.is_active, r.name AS role_name
     FROM users u
     JOIN roles r ON u.role_id = r.id
     WHERE u.phone = ? OR u.email = ?"
);
$stmt->bind_param('ss', $identifier, $identifier);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Incorrect phone/email or password']);
    exit;
}

if (!$user['is_active']) {
    http_response_code(403);
    echo json_encode(['error' => 'This account has been deactivated. Contact an admin.']);
    exit;
}

if ($user['role_name'] !== $expected_role) {
    http_response_code(403);
    echo json_encode(['error' => 'This account is not registered as a ' . $expected_role]);
    exit;
}

if ($user['role_name'] === 'official' && !$user['is_verified']) {
    http_response_code(403);
    echo json_encode(['error' => 'Your official account is still pending admin verification']);
    exit;
}

$_SESSION['user_id']   = $user['id'];
$_SESSION['role']      = $user['role_name'];
$_SESSION['full_name'] = $user['full_name'];

echo json_encode([
    'success'  => true,
    'role'     => $user['role_name'],
    'redirect' => $user['role_name'] === 'official' ? 'official_dashboard.php' : 'dashboard.php'
]);

$conn->close();