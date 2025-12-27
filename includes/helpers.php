<?php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function getFileIcon($fileType) {
    $icons = [
        'jpg' => 'fas fa-file-image',
        'jpeg' => 'fas fa-file-image',
        'png' => 'fas fa-file-image',
        'gif' => 'fas fa-file-image',
        'pdf' => 'fas fa-file-pdf',
        'doc' => 'fas fa-file-word',
        'docx' => 'fas fa-file-word',
        'txt' => 'fas fa-file-alt',
        'mp4' => 'fas fa-file-video',
        'mp3' => 'fas fa-file-audio',
        'zip' => 'fas fa-file-archive',
        'rar' => 'fas fa-file-archive',
        'default' => 'fas fa-file'
    ];
    
    $icon = $icons[strtolower($fileType)] ?? $icons['default'];
    
    return "<i class='{$icon}'></i>";
}

function translate($key, $lang = null) {
    global $languageSwitcher;
    
    if (!$lang) {
        $lang = $_SESSION['language'] ?? 'en';
    }
    
    // Load translations if not loaded
    if (!isset($languageSwitcher)) {
        $translationFile = 'assets/json/languages.json';
        if (file_exists($translationFile)) {
            $translations = json_decode(file_get_contents($translationFile), true);
            return $translations[$lang][$key] ?? $key;
        }
        return $key;
    }
    
    return $languageSwitcher->translate($key);
}

function isImage($fileType) {
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    return in_array(strtolower($fileType), $imageTypes);
}

function isVideo($fileType) {
    $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
    return in_array(strtolower($fileType), $videoTypes);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function maskEmail($email) {
    if (empty($email) || strpos($email, '@') === false) return $email;
    list($local, $domain) = explode('@', $email, 2);
    $localLen = strlen($local);
    if ($localLen <= 2) {
        $maskedLocal = substr($local, 0, 1) . str_repeat('*', max(0, $localLen - 1));
    } else {
        $maskedLocal = substr($local, 0, 2) . '***' . substr($local, -1);
    }
    return $maskedLocal . '@' . $domain;
}