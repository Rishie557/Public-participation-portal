<?php
header('Content-Type: application/json');
session_start();
require 'db_connect.php';

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

$docket = [];
if (!$is_admin) {
    $stmt = $conn->prepare("SELECT bill_slug FROM official_dockets WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $docket[] = $row['bill_slug'];
    }
    $stmt->close();
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

    $results[$slug] = [
        'yes' => $yes, 'no' => $no, 'total' => $total,
        'yes_pct' => $total ? round($yes / $total * 100) : 0,
        'no_pct'  => $total ? round($no  / $total * 100) : 0,
        'in_docket' => $is_admin || in_array($slug, $docket, true)
    ];
}

$conn->close();
echo json_encode($results);