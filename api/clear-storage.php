<?php
header('Content-Type: application/json');
require_once '../includes/session.php';
require_once '../includes/functions.php';

$session = new SessionManager();
if (!$session->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$userId = $session->getUserId();
$db = Database::getInstance();

// Fetch user's files (not trashed)
$res = $db->query('SELECT id, file_path, file_size FROM files WHERE user_id = ? AND is_trashed = 0', [$userId]);
$deletedCount = 0;
$freedBytes = 0;

if ($res && $res !== true) {
    while ($row = $res->fetch_assoc()) {
        $path = __DIR__ . '/../' . ltrim($row['file_path'], '/');
        if (file_exists($path) && is_file($path)) {
            $size = filesize($path);
            @unlink($path);
            $freedBytes += $size;
        }
        // Remove DB record
        $db->query('DELETE FROM files WHERE id = ?', [$row['id']]);
        $deletedCount++;
    }
}

// Log activity
$db->query('INSERT INTO activity_logs (user_id, activity_type, description) VALUES (?, "clear_storage", ?)', [$userId, "Cleared storage, deleted {$deletedCount} files"]);

echo json_encode(['success' => true, 'deleted' => $deletedCount, 'freed_bytes' => $freedBytes]);
