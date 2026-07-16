<?php
header('Content-Type: application/json');
session_start();
require 'config/db_connect.php';

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}

$sql = "SELECT pc.id, pc.change_type, pc.bill_slug, pc.payload, pc.proposed_at,
               u.full_name AS proposed_by_name
        FROM pending_changes pc
        JOIN users u ON u.id = pc.proposed_by
        WHERE pc.status = 'pending'
        ORDER BY pc.proposed_at ASC";

$result = $conn->query($sql);
$pending = [];
while ($row = $result->fetch_assoc()) {
    $row['payload'] = json_decode($row['payload'], true);
    $pending[] = $row;
}
$conn->close();
echo json_encode($pending);