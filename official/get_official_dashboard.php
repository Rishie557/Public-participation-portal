<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';
require __DIR__ . '/../includes/docket_helpers.php';

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
$slugs = $data['slugs'] ?? [];

$officialDepartment = null;
$officialLevel = null;
if (!$is_admin) {
    $deptStmt = $conn->prepare("SELECT office_department, government_level FROM users WHERE id = ?");
    $deptStmt->bind_param('i', $user_id);
    $deptStmt->execute();
    $deptRow = $deptStmt->get_result()->fetch_assoc();
    $deptStmt->close();
    $officialDepartment = $deptRow['office_department'] ?? null;
    $officialLevel = $deptRow['government_level'] ?? null;
}

$billGroups = $GLOBALS['billGroups'];
$billLevels = $GLOBALS['billLevels'];

$results = [];
foreach ($slugs as $slug) {
    $stmt = $conn->prepare("SELECT vote_value, COUNT(*) AS cnt FROM votes WHERE bill_slug = ? GROUP BY vote_value");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $res = $stmt->get_result();
    $yes = 0; $no = 0;
    while ($row = $res->fetch_assoc()) {
        if ($row['vote_value'] === 'yes') $yes = (int)$row['cnt'];
        if ($row['vote_value'] === 'no')  $no  = (int)$row['cnt'];
    }
    $stmt->close();
    $total = $yes + $no;

    $commentStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE bill_slug = ?");
    $commentStmt->bind_param('s', $slug);
    $commentStmt->execute();
    $commentRow = $commentStmt->get_result()->fetch_assoc();
    $commentStmt->close();
    $commentCount = (int)($commentRow['cnt'] ?? 0);

    $results[$slug] = [
        'yes' => $yes, 'no' => $no, 'total' => $total,
        'yes_pct' => $total ? round($yes / $total * 100) : 0,
        'no_pct'  => $total ? round($no  / $total * 100) : 0,
        'in_docket' => $is_admin || (
            departmentMatchesGroup($officialDepartment, $billGroups[$slug] ?? null)
            && levelMatches($officialLevel, $billLevels[$slug] ?? null)
        ),
        'comment_count' => $commentCount
    ];
}

$conn->close();
echo json_encode($results);