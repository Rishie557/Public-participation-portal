<?php
header('Content-Type: application/json');
require __DIR__ . '/../auth/admin_session_check.php';
require __DIR__ . '/../config/db_connect.php';

$sql = "SELECT u.id AS user_id, u.full_name, u.phone, u.email,
               u.county_name, u.constituency_name, u.office_id_number,
               u.created_at, ov.id AS verification_id
        FROM official_verifications ov
        JOIN users u ON u.id = ov.user_id
        WHERE ov.status = 'pending'
        ORDER BY u.created_at ASC";

$result = $conn->query($sql);
$pending = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($pending);
$conn->close();
