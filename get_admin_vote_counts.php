<?php
header('Content-Type: application/json');
require 'admin_session_check.php';
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$slugs = $data['slugs'] ?? [];

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
        'yes' => $yes,
        'no' => $no,
        'total' => $total,
        'yes_pct' => $total ? round($yes / $total * 100) : 0,
        'no_pct'  => $total ? round($no  / $total * 100) : 0
    ];
}

$conn->close();
echo json_encode($results);