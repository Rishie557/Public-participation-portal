<?php
require __DIR__ . '/../auth/admin_session_check.php';
require __DIR__ . '/../config/db_connect.php';

$result = $conn->query("SELECT * FROM reports ORDER BY created_at DESC");
$reports = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($reports);
$conn->close();