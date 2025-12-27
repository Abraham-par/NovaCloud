<?php
// Ensure JSON responses and hide raw PHP errors from leaking HTML to the client.
header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// Simple logger helper
function log_share_error($msg) {
    $file = __DIR__ . '/../logs/share-file.log';
    $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
    @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}

set_exception_handler(function($e) {
    log_share_error('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit();
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $msg = "PHP Error ({$errno}): {$errstr} in {$errfile}:{$errline}";
    log_share_error($msg);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit();
});

try {
    require_once '../includes/session.php';
    require_once '../includes/functions.php';

    $session = new SessionManager();
    $functions = new NovaCloudFunctions();

    if (!$session->isLoggedIn()) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }

    // Accept JSON body with: file_id, users (array or comma string), make_link (bool)
    $data = json_decode(file_get_contents('php://input'), true);
    $fileId = isset($data['file_id']) ? (int)$data['file_id'] : 0;
    $users = $data['users'] ?? [];
    $makeLink = !empty($data['make_link']);

    if ($fileId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid file id']);
        exit();
    }

    // Normalize users input
    if (is_string($users)) {
        $users = array_filter(array_map('trim', explode(',', $users)));
    }

    if (empty($users) && !$makeLink) {
        echo json_encode(['success' => false, 'error' => 'No users provided']);
        exit();
    }

    $userId = $session->getUserId();

    // Verify file ownership
    $db = new Database();
    $sql = "SELECT id FROM files WHERE id = ? AND user_id = ?";
    $result = $db->query($sql, [$fileId, $userId]);

    if (!$result || $result->num_rows == 0) {
        echo json_encode(['success' => false, 'error' => 'File not found or access denied']);
        exit();
    }

    // Resolve usernames to IDs
    $userIds = [];
    foreach ($users as $username) {
        $username = trim($username);
        if ($username === '') continue;
        $sql = "SELECT id FROM users WHERE username = ? LIMIT 1";
        $uRes = $db->query($sql, [$username]);
        if ($uRes && $uRes->num_rows > 0) {
            $r = $uRes->fetch_assoc();
            $userIds[] = (int)$r['id'];
        }
    }

    // If users were provided but none resolved, return an error
    if (!empty($users) && empty($userIds) && !$makeLink) {
        echo json_encode(['success' => false, 'error' => 'No valid users found']);
        exit();
    }

    // Merge with existing shared_with if present
    $fileRow = $db->query("SELECT shared_with FROM files WHERE id = ? LIMIT 1", [$fileId]);
    $existingShared = [];
    if ($fileRow && $fileRow->num_rows > 0) {
        $fr = $fileRow->fetch_assoc();
        $existingShared = json_decode($fr['shared_with'] ?? '[]', true) ?: [];
    }

    $newShared = array_values(array_unique(array_merge($existingShared, $userIds)));

    // Update files table shared_with; set is_public if link requested
    $sqlUpdate = "UPDATE files SET shared_with = ?" . ($makeLink ? ", is_public = 1" : "") . " WHERE id = ?";
    $sharedJson = json_encode($newShared);
    $ok = $db->query($sqlUpdate, [$sharedJson, $fileId]);

    // Optionally create public share token
    $shareLink = null;
    if ($makeLink) {
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
        $insert = $db->query("INSERT INTO file_shares (file_id, token, created_by) VALUES (?, ?, ?)", [$fileId, $token, $userId]);
        if ($insert) {
            $shareLink = SITE_URL . 'download.php?token=' . $token;
        }
    }

    // Log activity
    $sql = "INSERT INTO activity_logs (user_id, activity_type, description) VALUES (?, 'share', ?)";
    $description = "Shared file #{$fileId} with " . count($newShared) . " user(s)" . ($makeLink ? ' and created public link' : '');
    $db->query($sql, [$userId, $description]);

    echo json_encode(['success' => true, 'share_link' => $shareLink, 'shared_with' => $newShared]);

} catch (Throwable $t) {
    // Catch any remaining errors
    log_share_error('Fatal: ' . $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit();
}