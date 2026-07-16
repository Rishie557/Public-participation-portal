<?php
header('Content-Type: application/json');
session_start();

$ADMIN_USER = 'admin';
$ADMIN_PASS = 'sauti2026'; // change this to whatever you want

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
  $_SESSION['admin_logged_in'] = true;
  echo json_encode(['success' => true]);
} else {
  http_response_code(401);
  echo json_encode(['error' => 'Incorrect username or password.']);
}