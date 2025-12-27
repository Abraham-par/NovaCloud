<?php
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

$session = new SessionManager();
$session->requireAdmin();
$functions = new NovaCloudFunctions();
$db = Database::getInstance();

header('Content-Type: application/json');
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Missing user id']);
    exit;
}

$user = $functions->getUser($userId);
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Last login IP: try login_logs table
$lastIp = null;
try {
    $res = $db->query('SELECT ip_address, created_at FROM login_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 1', [$userId]);
    if ($res && $row = $res->fetch_assoc()) {
        $lastIp = $row['ip_address'] ?? null;
        $lastLoginAt = $row['created_at'] ?? null;
    }
} catch (Exception $e) {
    // ignore if table not present
}

// Activity history: try activity_logs and login_logs
$activities = [];
try {
    $rows = $db->select('activity_logs', '*', 'performed_by = ?', [$userId], 'created_at DESC', 50);
    if ($rows) {
        foreach ($rows as $r) {
            $activities[] = [
                'type' => 'activity',
                'action' => $r['action'] ?? ($r['message'] ?? ''),
                'created_at' => $r['created_at'] ?? null,
            ];
        }
    }
} catch (Exception $e) {
    // ignore
}

try {
    $logins = $db->select('login_logs', '*', 'user_id = ?', [$userId], 'created_at DESC', 50);
    if ($logins) {
        foreach ($logins as $l) {
            $activities[] = [
                'type' => 'login',
                'ip' => $l['ip_address'] ?? null,
                'created_at' => $l['created_at'] ?? null,
            ];
        }
    }
} catch (Exception $e) {
    // ignore
}

// sort activities by created_at desc
usort($activities, function($a, $b) {
    $ta = strtotime($a['created_at'] ?? '1970-01-01');
    $tb = strtotime($b['created_at'] ?? '1970-01-01');
    return $tb <=> $ta;
});

$out = [
    'success' => true,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'] ?? '',
        'email' => $user['email'] ?? '',
        'full_name' => $user['full_name'] ?? '',
        'created_at' => $user['created_at'] ?? null,
        'last_login' => $user['last_login'] ?? null,
        'last_login_ip' => $lastIp ?? null,
    ],
    'activities' => $activities,
];

echo json_encode($out);

