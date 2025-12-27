<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

require_once '../includes/session.php';
require_once '../includes/functions.php';

$session = new SessionManager();
if (!$session->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$fileId = isset($_GET['file']) ? (int)$_GET['file'] : 0;
if ($fileId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid file id']);
    exit();
}

$userId = $session->getUserId();
$db = new Database();

// Verify file ownership
$res = $db->query("SELECT id FROM files WHERE id = ? AND user_id = ?", [$fileId, $userId]);
if (!$res || $res->num_rows == 0) {
    echo json_encode(['success' => false, 'error' => 'File not found or access denied']);
    exit();
}

// Ensure file_shares table exists
$db->query("CREATE TABLE IF NOT EXISTS file_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    token VARCHAR(128) NOT NULL,
    created_by INT DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$token = bin2hex(random_bytes(16));
$ok = $db->query("INSERT INTO file_shares (file_id, token, created_by) VALUES (?, ?, ?)", [$fileId, $token, $userId]);
if (!$ok) {
    echo json_encode(['success' => false, 'error' => 'Failed to create share link']);
    exit();
}

// Mark file as public
$db->query("UPDATE files SET is_public = 1 WHERE id = ?", [$fileId]);

$shareLink = SITE_URL . 'download.php?token=' . $token;
echo json_encode(['success' => true, 'share_link' => $shareLink]);
