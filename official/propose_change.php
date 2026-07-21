<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/../config/db_connect.php';
require __DIR__ . '/../includes/docket_helpers.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'official') {
    http_response_code(403);
    echo json_encode(['error' => 'Access restricted.']);
    exit;
}

$user_id = $_SESSION['user_id'];

$verCheck = $conn->prepare("SELECT status FROM official_verifications WHERE user_id = ?");
$verCheck->bind_param('i', $user_id);
$verCheck->execute();
$verRow = $verCheck->get_result()->fetch_assoc();
$verCheck->close();

if (!$verRow || $verRow['status'] !== 'approved') {
    http_response_code(403);
    echo json_encode(['error' => 'Your official registration is not yet approved.']);
    exit;
}

// ── Read the request body two ways: multipart (file upload, used only by
//    add_bill) vs raw JSON (everything else, unchanged from before). ──
$isMultipart = isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;

if ($isMultipart) {
    $change_type = $_POST['change_type'] ?? '';
    $bill_slug = trim($_POST['bill_slug'] ?? '');
    $payload = json_decode($_POST['payload'] ?? '{}', true) ?? [];
} else {
    $data = json_decode(file_get_contents('php://input'), true);
    $change_type = $data['change_type'] ?? '';
    $bill_slug = trim($data['bill_slug'] ?? '');
    $payload = $data['payload'] ?? [];
}

if (!in_array($change_type, ['add_bill', 'remove_bill', 'edit_tax_spend'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid change type.']);
    exit;
}

// add_bill has no existing slug to check docket against — open to any approved official.
// remove_bill / edit_tax_spend require docket ownership of the existing bill.
if ($change_type !== 'add_bill') {
    if (!$bill_slug) {
        http_response_code(400);
        echo json_encode(['error' => 'bill_slug is required.']);
        exit;
    }

    if (!officialOwnsBill($conn, $user_id, $bill_slug)) {
        http_response_code(403);
        echo json_encode(['error' => 'This bill is not in your docket.']);
        exit;
    }
}

if ($change_type === 'add_bill') {
    if (empty($payload['slug']) || empty($payload['title']) || empty($payload['bill_status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'slug, title, and bill_status are required to propose a new bill.']);
        exit;
    }
    $bill_slug = trim($payload['slug']);

    // ── Required PDF attachment ──
    if (!$isMultipart || empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
        http_response_code(400);
        echo json_encode(['error' => 'A bill document (PDF) is required.']);
        exit;
    }

    $file = $_FILES['document'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File upload failed. Please try again.']);
        exit;
    }

    if ($file['size'] > 15 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'File is too large (max 15MB).']);
        exit;
    }

    // Verify actual file content is a PDF, not just a renamed file
    // (client-side accept="application/pdf" can be bypassed).
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        http_response_code(400);
        echo json_encode(['error' => 'Only PDF files are accepted.']);
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/bill_documents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower($bill_slug));
    $filename = $safeSlug . '_' . bin2hex(random_bytes(6)) . '.pdf';
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not save the uploaded file.']);
        exit;
    }

    // Store a relative path (not the filesystem path) for later use in links.
    $payload['document_path'] = 'uploads/bill_documents/' . $filename;
    $payload['document_original_name'] = basename($file['name']);
}

$payload_json = json_encode($payload);

$stmt = $conn->prepare(
    "INSERT INTO pending_changes (change_type, bill_slug, payload, proposed_by)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('sssi', $change_type, $bill_slug, $payload_json, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'message' => 'Change submitted for admin review']);