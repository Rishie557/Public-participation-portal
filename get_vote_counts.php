<?php
header('Content-Type: application/json');
session_start();
require 'config/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$slugs = $data['slugs'] ?? [];

if (!is_array($slugs) || empty($slugs)) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($slugs), '?'));
$types = str_repeat('s', count($slugs));

$stmt = $conn->prepare("SELECT bill_slug, vote_value, COUNT(*) AS cnt FROM votes WHERE bill_slug IN ($placeholders) GROUP BY bill_slug, vote_value");
$stmt->bind_param($types, ...$slugs);
$stmt->execute();
$result = $stmt->get_result();

$counts = [];
foreach ($slugs as $slug) { $counts[$slug] = ['yes' => 0, 'no' => 0]; }
while ($row = $result->fetch_assoc()) {
    $counts[$row['bill_slug']][$row['vote_value']] = (int)$row['cnt'];
}
$stmt->close();

$user_votes = [];
if (!empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt2 = $conn->prepare("SELECT bill_slug, vote_value FROM votes WHERE user_id = ? AND bill_slug IN ($placeholders)");
    $types2 = 'i' . $types;
    $params = array_merge([$user_id], $slugs);
    $stmt2->bind_param($types2, ...$params);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($row = $res2->fetch_assoc()) {
        $user_votes[$row['bill_slug']] = $row['vote_value'];
    }
    $stmt2->close();
}

$response = [];
foreach ($slugs as $slug) {
    $yes = $counts[$slug]['yes'];
    $no  = $counts[$slug]['no'];
    $total = $yes + $no;
    $response[$slug] = [
        'yes' => $yes, 'no' => $no, 'total' => $total,
        'yes_pct' => $total ? round($yes / $total * 100) : 0,
        'no_pct'  => $total ? round($no  / $total * 100) : 0,
        'user_vote' => $user_votes[$slug] ?? null
    ];
}

echo json_encode($response);
$conn->close();