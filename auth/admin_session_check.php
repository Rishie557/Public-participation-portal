<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
  http_response_code(401);
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Not authenticated.']);
  exit;
}