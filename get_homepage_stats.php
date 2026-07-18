<?php
header('Content-Type: application/json');
require __DIR__ . '/config/db_connect.php';

$totalVotesResult = $conn->query("SELECT COUNT(*) AS cnt FROM votes");
$totalVotes = (int) $totalVotesResult->fetch_assoc()['cnt'];

$activeBillsResult = $conn->query(
    "SELECT COUNT(*) AS cnt FROM bills
     WHERE status = 'active' AND bill_status != 'Signed into Law'"
);
$activeBills = (int) $activeBillsResult->fetch_assoc()['cnt'];

$passedBillsResult = $conn->query(
    "SELECT COUNT(*) AS cnt FROM bills
     WHERE status = 'active' AND bill_status = 'Signed into Law'"
);
$passedBills = (int) $passedBillsResult->fetch_assoc()['cnt'];

$conn->close();

echo json_encode([
    'total_votes' => $totalVotes,
    'active_bills' => $activeBills,
    'passed_bills' => $passedBills,
]);