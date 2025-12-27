<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nova_cloud');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'NovaCloud');
// Correct local development URL
define('SITE_URL', 'http://localhost/myproject/NovaCloudV2/');
define('SITE_PATH', dirname(dirname(__FILE__)) . '/');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 104857600); // 100MB
define('ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'mp4', 'mp3']);

// Security configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 7200); // 2 hours

// Language configuration
define('DEFAULT_LANGUAGE', 'en');
define('AVAILABLE_LANGUAGES', ['en', 'am', 'om']);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict'
    ]);
}

// Set default timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', SITE_PATH . 'logs/error.log');

// Create logs directory if it doesn't exist
if (!file_exists(SITE_PATH . 'logs')) {
    mkdir(SITE_PATH . 'logs', 0755, true);
}

// Set CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set default language if not set
if (empty($_SESSION['language'])) {
    $_SESSION['language'] = DEFAULT_LANGUAGE;
}