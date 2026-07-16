<?php
header('Content-Type: application/json');
require 'db_connect.php';

$result = $conn->query(
    "SELECT slug, title, bill_status, group_label
     FROM bills
     WHERE status = 'active'
     ORDER BY group_label, id"
);

$bills = [];
while ($row = $result->fetch_assoc()) {
    $bills[] = $row;
}
$conn->close();
echo json_encode($bills);