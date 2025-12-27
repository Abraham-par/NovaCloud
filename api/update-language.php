<?php
require_once '../includes/config.php';
require_once '../includes/database.php';

header('Content-Type: application/json');

// Check CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate language
$language = $_POST['language'] ?? '';
if (!in_array($language, ['en', 'am', 'om'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid language']);
    exit();
}

// Update language in session
$_SESSION['language'] = $language;

// Update language in database if user is logged in
if (isset($_SESSION['user_id'])) {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    $result = $db->update('users', ['language' => $language], 'id = ?', [$userId]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
} else {
    echo json_encode(['success' => true]);
}