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
$db = new Database();

// Get recent activity
$sql = "SELECT * FROM activity_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 20";

$result = $db->query($sql, [$userId]);

$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = [
        'id' => $row['id'],
        'type' => $row['activity_type'],
        'description' => $row['description'],
        'time' => timeAgo($row['created_at']),
        'icon' => getActivityIcon($row['activity_type'])
    ];
}

echo json_encode(['success' => true, 'activities' => $activities]);

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

function getActivityIcon($type) {
    $icons = [
        'login' => 'fa-sign-in-alt',
        'logout' => 'fa-sign-out-alt',
        'upload' => 'fa-upload',
        'download' => 'fa-download',
        'delete' => 'fa-trash',
        'share' => 'fa-share',
        'profile_update' => 'fa-user-edit',
        'password_change' => 'fa-key',
        'security_question' => 'fa-question-circle'
    ];
    
    return $icons[$type] ?? 'fa-info-circle';
}