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

$userId = $session->getUserId();
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $db = new Database();
    
    // Update notification preferences
    if (isset($data['notifications'])) {
        $sql = "UPDATE users SET notification_preferences = ? WHERE id = ?";
        $db->query($sql, [json_encode($data['notifications']), $userId]);
    }
    
    // Update privacy settings
    if (isset($data['privacy'])) {
        $sql = "UPDATE users SET privacy_settings = ? WHERE id = ?";
        $db->query($sql, [json_encode($data['privacy']), $userId]);
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No data provided']);
}