<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to vote.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$bill_slug = trim($data['bill_slug'] ?? '');
$raw_vote = $data['vote'] ?? '';
$vote_value = in_array($raw_vote, ['yes', 'no'], true) ? $raw_vote : null;

$valid_slugs = [
  'finance-bill-2026','appropriation-bill-2026','supp-approp-2026','division-revenue-2026',
  'county-alloc-2026','infra-fund-2026','food-feed-safety','plant-protection','forest-conservation',
  'competition-amendment','procurement-amendment','culture-bill','health-amendment'
];

if (!$bill_slug || !$vote_value || !in_array($bill_slug, $valid_slugs, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid vote submission.']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO votes (user_id, bill_slug, vote_value) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $user_id, $bill_slug, $vote_value);

if (!$stmt->execute()) {
    if ($conn->errno === 1062) {
        http_response_code(409);
        echo json_encode(['error' => 'You have already voted on this bill.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Could not record your vote.']);
    }
    exit;
}
$stmt->close();

echo json_encode(['success' => true, 'counts' => getBillCounts($conn, $bill_slug)]);
$conn->close();

function getBillCounts($conn, $slug) {
    $stmt = $conn->prepare("SELECT vote_value, COUNT(*) AS cnt FROM votes WHERE bill_slug = ? GROUP BY vote_value");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $yes = 0; $no = 0;
    while ($row = $result->fetch_assoc()) {
        if ($row['vote_value'] === 'yes') $yes = (int)$row['cnt'];
        if ($row['vote_value'] === 'no')  $no  = (int)$row['cnt'];
    }
    $stmt->close();
    $total = $yes + $no;
    return [
        'yes' => $yes, 'no' => $no, 'total' => $total,
        'yes_pct' => $total ? round($yes / $total * 100) : 0,
        'no_pct'  => $total ? round($no  / $total * 100) : 0
    ];
}