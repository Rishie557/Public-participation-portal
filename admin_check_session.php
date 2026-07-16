<?php
header('Content-Type: application/json');
session_start();

if (!empty($_SESSION['admin_logged_in'])) {
  echo json_encode(['logged_in' => true]);
} else {
  echo json_encode(['logged_in' => false]);
}