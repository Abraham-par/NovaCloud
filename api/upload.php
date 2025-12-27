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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit();
}

$userId = $session->getUserId();

if (isset($_FILES['file'])) {
    $fileId = $functions->uploadFile($userId, $_FILES['file']);
    
    if ($fileId) {
        echo json_encode(['success' => true, 'file_id' => $fileId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Upload failed']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file provided']);
}