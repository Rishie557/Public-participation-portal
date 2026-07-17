<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

$bill_slug = trim($_GET['bill_slug'] ?? '');

$valid_slugs = [
  'finance-bill-2026','appropriation-bill-2026','supp-approp-2026','division-revenue-2026',
  'county-alloc-2026','infra-fund-2026','food-feed-safety','plant-protection','forest-conservation',
  'competition-amendment','procurement-amendment','culture-bill','health-amendment'
];

if (!$bill_slug || !in_array($bill_slug, $valid_slugs, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid bill.']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT c.id, c.comment_text, c.created_at, u.full_name
     FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.bill_slug = ? AND c.status = 'active'
     ORDER BY c.created_at DESC
     LIMIT 100"
);
$stmt->bind_param('s', $bill_slug);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'id' => (int)$row['id'],
        'name' => $row['full_name'],
        'text' => $row['comment_text'],
        'time' => date('j M, H:i', strtotime($row['created_at']))
    ];
}
$stmt->close();
$conn->close();

echo json_encode($comments);