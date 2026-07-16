<?php
header('Content-Type: application/json');
require 'admin_session_check.php';
require 'db_connect.php';

$allowed = ['pending', 'approved', 'rejected'];
$status = $_GET['status'] ?? 'pending';
if (!in_array($status, $allowed, true)) {
    $status = 'pending';
}

$sql = "SELECT u.id AS user_id, u.full_name, u.phone, u.email,
               u.county_name, u.constituency_name, u.office_id_number,
               u.created_at, ov.status, ov.notes, ov.reviewed_at
        FROM official_verifications ov
        JOIN users u ON u.id = ov.user_id
        WHERE ov.status = ?
        ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $status);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($rows);
$conn->close();