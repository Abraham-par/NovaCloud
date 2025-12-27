<?php
header('Content-Type: application/json');
require_once '../includes/session.php';
require_once '../includes/functions.php';

$session = new SessionManager();
if (!$session->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Expect POST with reason and confirmations
$data = json_decode(file_get_contents('php://input'), true);
$confirm1 = !empty($data['confirm_understand']) ? true : false;
$confirm2 = !empty($data['confirm_delete']) ? true : false;
$reason = trim($data['reason'] ?? '');

if (!$confirm1 || !$confirm2) {
    echo json_encode(['success' => false, 'error' => 'Please confirm you understand the consequences']);
    exit();
}

$userId = $session->getUserId();
$db = Database::getInstance();

// Mark account as deactivated (account_status field used elsewhere)
$ok = $db->query("UPDATE users SET account_status = 'deactivated', deactivated_at = ? WHERE id = ?", [date('Y-m-d H:i:s'), $userId]);

// Log reason
$db->query('INSERT INTO activity_logs (user_id, activity_type, description) VALUES (?, "deactivate", ?)', [$userId, "User deactivated account. Reason: " . $reason]);

// Optionally remove sessions/cookies
// Destroy current session
session_unset();
session_destroy();

echo json_encode(['success' => true]);
