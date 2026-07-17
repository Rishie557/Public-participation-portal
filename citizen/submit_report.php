<?php
require __DIR__ . '/../config/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$description = $data['description'] ?? '';

if (!$description || strlen(trim($description)) < 20) {
    http_response_code(400);
    echo json_encode(['error' => 'Comment too short']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO reports (description) VALUES (?)");
$stmt->bind_param("s", $description);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Insert failed']);
}

$stmt->close();
$conn->close();
