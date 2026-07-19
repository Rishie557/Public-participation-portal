<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

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

// Server-side bill -> group/level mapping (kept here, not trusted from the client,
// since in_docket controls real permissions like posting responses and moderating comments).
$billGroups = [
    'finance-bill-2026'       => 'Finance',
    'appropriation-bill-2026' => 'Finance',
    'supp-approp-2026'        => 'Finance',
    'division-revenue-2026'   => 'Finance',
    'county-alloc-2026'       => 'Finance',
    'infra-fund-2026'         => 'Infrastructure',
    'food-feed-safety'        => 'Agriculture',
    'plant-protection'        => 'Agriculture',
    'forest-conservation'     => 'Environment',
    'competition-amendment'   => 'Trade',
    'procurement-amendment'   => 'Procurement',
    'culture-bill'            => 'Culture',
    'health-amendment'        => 'Health',
];

// All current bills are national-level (passed by the National Assembly/Senate),
// including the ones about county allocations — none are county-assembly bills yet.
$billLevels = [
    'finance-bill-2026'       => 'national',
    'appropriation-bill-2026' => 'national',
    'supp-approp-2026'        => 'national',
    'division-revenue-2026'   => 'national',
    'county-alloc-2026'       => 'national',
    'infra-fund-2026'         => 'national',
    'food-feed-safety'        => 'national',
    'plant-protection'        => 'national',
    'forest-conservation'     => 'national',
    'competition-amendment'   => 'national',
    'procurement-amendment'   => 'national',
    'culture-bill'            => 'national',
    'health-amendment'        => 'national',
];

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

function departmentMatchesGroup(?string $department, ?string $group): bool {
    if (!$department || !$group) return false;
    if ($department === 'All') return true;
    return $department === $group;
}

function levelMatches(?string $officialLevel, ?string $billLevel): bool {
    if (!$officialLevel || !$billLevel) return false;
    return $officialLevel === $billLevel;
}

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