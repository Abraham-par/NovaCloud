<?php
header('Content-Type: application/json');
require_once '../includes/session.php';
require_once '../includes/functions.php';

$session = new SessionManager();
$functions = new NovaCloudFunctions();

if (!$session->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$fileId = $data['file_id'] ?? null;

if ($fileId) {
    $success = $functions->deleteFile($fileId, $session->getUserId());
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'error' => 'No file ID provided']);
}