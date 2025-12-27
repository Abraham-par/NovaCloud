<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

// Allow token access or session-based access
$token = $_GET['token'] ?? '';
$fileId = isset($_GET['file']) ? (int)$_GET['file'] : null;

$db = Database::getInstance();

// Helper to serve file safely
function serveFile($fileRow) {
    $path = __DIR__ . '/' . $fileRow['file_path'];
    $real = realpath($path);
    $uploadsDir = realpath(__DIR__ . '/' . UPLOAD_DIR);

    if (!$real || strpos($real, $uploadsDir) !== 0 || !file_exists($real)) {
        http_response_code(404);
        echo 'File not found.';
        exit();
    }

    // Send headers and stream
    header('Content-Description: File Transfer');
    header('Content-Type: ' . ($fileRow['mime_type'] ?? 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . basename($fileRow['original_name'] ?? $fileRow['filename']) . '"');
    header('Content-Length: ' . filesize($real));
    header('Cache-Control: private');

    readfile($real);
    exit();
}

if ($token) {
    // Lookup token in file_shares
    $row = $db->query('SELECT fs.file_id, f.* FROM file_shares fs JOIN files f ON fs.file_id = f.id WHERE fs.token = ? LIMIT 1', [$token]);
    if ($row && $row->num_rows > 0) {
        $fileRow = $row->fetch_assoc();
        serveFile($fileRow);
    }
    http_response_code(404);
    echo 'Invalid or expired token.';
    exit();
}

session_start();
$sessionUser = $_SESSION['user_id'] ?? null;

if ($fileId) {
    // Fetch file
    $res = $db->query('SELECT * FROM files WHERE id = ? LIMIT 1', [$fileId]);
    if (!$res || $res->num_rows == 0) {
        http_response_code(404);
        echo 'File not found.';
        exit();
    }
    $fileRow = $res->fetch_assoc();

    // Owner can download
    if ($fileRow['user_id'] == $sessionUser) {
        serveFile($fileRow);
    }

    // Public file
    if (!empty($fileRow['is_public'])) {
        serveFile($fileRow);
    }

    // Shared with current user
    $sharedWith = json_decode($fileRow['shared_with'] ?? '[]', true) ?: [];
    if (in_array($sessionUser, $sharedWith)) {
        serveFile($fileRow);
    }

    http_response_code(403);
    echo 'You do not have permission to download this file.';
    exit();
}

http_response_code(400);
echo 'No file specified.';
exit();
