<?php
session_start();
require 'db_connect.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

if (empty($_SESSION['role']) || !in_array($_SESSION['role'], ['official', 'admin'], true)) {
    http_response_code(403);
    die('Access restricted to government officials.');
}

if ($_SESSION['role'] === 'official') {
    $stmt = $conn->prepare("SELECT status FROM official_verifications WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || $row['status'] !== 'approved') {
        http_response_code(403);
        die('Your government official registration is still pending admin approval.');
    }
}
$conn->close();