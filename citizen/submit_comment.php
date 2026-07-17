<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to comment.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$bill_slug = trim($data['bill_slug'] ?? '');
$comment   = trim($data['comment'] ?? '');

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

if (mb_strlen($comment) < 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Comment is too short.']);
    exit;
}

if (mb_strlen($comment) > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'Comment is too long (max 1000 characters).']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Basic per-user rate limit: max 1 comment per bill per 30 seconds
$check = $conn->prepare("SELECT created_at FROM comments WHERE user_id = ? AND bill_slug = ? ORDER BY created_at DESC LIMIT 1");
$check->bind_param('is', $user_id, $bill_slug);
$check->execute();
$last = $check->get_result()->fetch_assoc();
$check->close();

if ($last && (time() - strtotime($last['created_at'])) < 30) {
    http_response_code(429);
    echo json_encode(['error' => 'Please wait a moment before commenting again.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO comments (user_id, bill_slug, comment_text, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param('iss', $user_id, $bill_slug, $comment);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not post your comment.']);
    exit;
}
$stmt->close();

echo json_encode(['success' => true]);
$conn->close();