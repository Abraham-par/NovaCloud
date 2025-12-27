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

$search = $_GET['q'] ?? '';
$userId = $session->getUserId();

$files = $functions->getUserFiles($userId, $search);

echo json_encode(['success' => true, 'files' => $files]);