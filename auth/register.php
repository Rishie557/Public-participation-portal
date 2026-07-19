<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require '../config/db_connect.php';

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

// Only these exact values are accepted for an official's ministry/docket.
// This must match the visible <option> values in login.html and the $billGroups map in get_official_dashboard.php.
$SPECIFIC_DEPARTMENTS = ['Finance', 'Infrastructure', 'Agriculture', 'Environment', 'Trade', 'Procurement', 'Culture', 'Health'];

// Positions must match login.js's NATIONAL_POSITIONS / COUNTY_POSITIONS exactly.
$NATIONAL_POSITIONS = ['President', 'Deputy President', 'Cabinet Secretary', 'Member of Parliament (MP)', 'Senator', 'Clerk (National Assembly/Senate)'];
$COUNTY_POSITIONS = ['Governor', 'Deputy Governor', 'County Executive Committee Member (CECM)', 'Member of County Assembly (MCA)', 'County Clerk'];
$MINISTRY_POSITIONS = ['Cabinet Secretary', 'County Executive Committee Member (CECM)'];

if ($role === 'official') {
    if (!in_array($government_level, ['national', 'county'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Please select a valid government level.']);
        exit;
    }

    $validPositionsForLevel = $government_level === 'national' ? $NATIONAL_POSITIONS : $COUNTY_POSITIONS;
    if (!in_array($position_title, $validPositionsForLevel, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Please select a valid position for the chosen government level.']);
        exit;
    }

    $isMinistryPosition = in_array($position_title, $MINISTRY_POSITIONS, true);
    if ($isMinistryPosition && !in_array($office_department, $SPECIFIC_DEPARTMENTS, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Please select a valid ministry/docket from the list.']);
        exit;
    }
    if (!$isMinistryPosition && $office_department !== 'All') {
        // Non-ministry positions (chief executives, legislators, clerks) always get the "All" docket —
        // never trust a client-submitted single-ministry value for these roles.
        $office_department = 'All';
    }
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