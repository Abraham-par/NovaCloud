<?php
require_once 'database.php';

class NovaCloudFunctions {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function registerUser($data) {
        // Basic validation
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return false;
        }
        $username = $this->db->escape($data['username']);
        $email = $this->db->escape($data['email']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $full_name = $this->db->escape($data['full_name'] ?? '');
        $security_question = $this->db->escape($data['security_question'] ?? '');
        $security_answer = $this->db->escape($data['security_answer'] ?? '');

        // Check if username or email already exists
        $exists = $this->db->count('users', 'username = ? OR email = ?', [$username, $email]);
        if ($exists > 0) {
            return false;
        }

        $insertData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'security_question' => $security_question,
            'security_answer' => $security_answer,
            'language' => $_SESSION['language'] ?? 'en'
        ];

        $insertId = $this->db->insert('users', $insertData);

        return $insertId !== false;
    }
    
    public function authenticateUser($username, $password) {
        $username = $this->db->escape($username);
        
        $sql = "SELECT id, username, password, user_type FROM users 
                WHERE (username = ? OR email = ?) AND account_status = 'active'";
        
        $result = $this->db->query($sql, [$username, $username]);
        
        if ($result && $row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        
        return false;
    }
    
    public function getUserFiles($userId, $search = '') {
        $files = [];

        // Build base search condition
        $searchSql = '';
        $params = [];
        if ($search !== '') {
            $like = '%' . $this->db->escape($search) . '%';
            $searchSql = " AND (filename LIKE ? OR original_name LIKE ? )";
            $params[] = $like;
            $params[] = $like;
        }

        // Fetch files owned by user
        $owned = $this->db->select('files', '*', "user_id = ? AND is_trashed = 0" . $searchSql, array_merge([$userId], $params), 'uploaded_at DESC');
        $owned = $owned ?: [];

        // Fetch files shared with user (shared_with JSON contains user id) or public
        // Try JSON_CONTAINS first; fallback to LIKE
        $shared = [];
        $jsonSupported = true;
        try {
            $q = "SELECT * FROM files WHERE is_trashed = 0 AND (is_public = 1 OR JSON_CONTAINS(shared_with, JSON_ARRAY(?)))" . ($search !== '' ? " AND (filename LIKE ? OR original_name LIKE ?)" : "") . " ORDER BY uploaded_at DESC";
            $qParams = [$userId];
            if ($search !== '') {
                $qParams[] = $like;
                $qParams[] = $like;
            }
            $res = $this->db->query($q, $qParams);
            if ($res && $res !== true) {
                while ($r = $res->fetch_assoc()) $shared[] = $r;
            }
        } catch (Exception $e) {
            $jsonSupported = false;
        }

        if (!$jsonSupported || empty($shared)) {
            // Fallback: use LIKE to find user id in shared_with string
            $likeId = '%"' . (int)$userId . '"%';
            $fallbackQ = 'SELECT * FROM files WHERE is_trashed = 0 AND (is_public = 1 OR shared_with LIKE ?)'
                        . ($search !== '' ? ' AND (filename LIKE ? OR original_name LIKE ?)' : '') . ' ORDER BY uploaded_at DESC';
            $fallbackParams = [$likeId];
            if ($search !== '') {
                $fallbackParams[] = $like;
                $fallbackParams[] = $like;
            }
            $res2 = $this->db->query($fallbackQ, $fallbackParams);
            if ($res2 && $res2 !== true) {
                while ($r = $res2->fetch_assoc()) $shared[] = $r;
            }
        }

        // Merge owned and shared lists, unique by id
        $all = [];
        $seen = [];
        foreach (array_merge($owned, $shared) as $f) {
            if (isset($seen[$f['id']])) continue;
            $seen[$f['id']] = true;
            $all[] = $f;
        }

        return $all;
    }

    public function getUserStats($userId) {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'last_upload' => null
        ];

        $totalFiles = $this->db->count('files', 'user_id = ? AND is_trashed = 0', [$userId]);
        $stats['total_files'] = $totalFiles;

        $sql = "SELECT COALESCE(SUM(file_size), 0) AS total_size, MAX(uploaded_at) AS last_upload FROM files WHERE user_id = ? AND is_trashed = 0";
        $result = $this->db->query($sql, [$userId]);
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_size'] = (int)$row['total_size'];
            $stats['last_upload'] = $row['last_upload'];
        }

        return $stats;
    }
    
    public function updateProfile($userId, $data) {
        $allowed = ['full_name', 'language', 'notification_preferences', 'theme'];
        $update = [];
        foreach ($data as $k => $v) {
            if (in_array($k, $allowed)) {
                $update[$k] = $this->db->escape($v);
            }
        }

        if (empty($update)) {
            return false;
        }

        return (bool)$this->db->update('users', $update, 'id = ?', [$userId]);
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        $row = $this->db->getRow('users', 'id, password', 'id = ?', [$userId]);
        if (!$row) return false;

        if (!password_verify($currentPassword, $row['password'])) {
            return false;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return (bool)$this->db->update('users', ['password' => $newHash], 'id = ?', [$userId]);
    }

    public function uploadFile($userId, $file) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $maxSize = defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : (100 * 1024 * 1024);
        if ($file['size'] > $maxSize) {
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = defined('ALLOWED_TYPES') ? ALLOWED_TYPES : [];
        if (!empty($allowed) && !in_array($ext, $allowed)) {
            return false;
        }

        $uploadDir = defined('UPLOAD_DIR') ? rtrim(UPLOAD_DIR, '/') . '/' : 'uploads/';
        $fullDir = __DIR__ . '/../' . $uploadDir;
        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($file['name']));
        $newName = $userId . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destination = $fullDir . $newName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return false;
        }

        $filePath = $uploadDir . $newName;
        $mime = $file['type'] ?? mime_content_type($destination);

        $insertData = [
            'user_id' => $userId,
            'filename' => $newName,
            'original_name' => $safeName,
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $ext,
            'mime_type' => $mime,
            'is_folder' => 0,
            'is_public' => 0,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];

        $insertId = $this->db->insert('files', $insertData);
        return $insertId ?: false;
    }

    public function deleteFile($fileId, $userId) {
        $row = $this->db->getRow('files', '*', 'id = ? AND user_id = ?', [$fileId, $userId]);
        if (!$row) return false;

        // Soft delete: mark trashed and set deleted_at
        $now = date('Y-m-d H:i:s');
        return (bool)$this->db->update('files', ['is_trashed' => 1, 'deleted_at' => $now], 'id = ? AND user_id = ?', [$fileId, $userId]);
    }

    // Helper function to format file sizes
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function getSystemStats() {
        $stats = [
            'users' => 0,
            'files' => 0,
            'countries' => 0
        ];

        // Total users
        $stats['users'] = $this->db->count('users');

        // Total files
        $stats['files'] = $this->db->count('files');

        // Countries served (approx using distinct language as proxy if country not stored)
        $result = $this->db->query('SELECT COUNT(DISTINCT language) AS cnt FROM users');
        if ($result && $row = $result->fetch_assoc()) {
            $stats['countries'] = max(1, (int)$row['cnt']);
        }

        return $stats;
    }

    /**
     * Return all users (optionally limited) ordered by newest first
     */
    public function getAllUsers($limit = 0) {
        $order = 'created_at DESC';
        $limitSql = $limit && is_int($limit) && $limit > 0 ? $limit : '';
        $rows = $this->db->select('users', '*', '', [], $order, $limitSql);
        if (!$rows) return [];

        // Enrich users with storage_used and last_login if possible
        foreach ($rows as &$r) {
            $r['storage_used'] = $this->getUserStorageUsage($r['id']);

            // try to get last login from login_logs or users.last_login
            $last = null;
            $res = $this->db->query('SELECT MAX(created_at) AS last_login FROM login_logs WHERE user_id = ?', [$r['id']]);
            if ($res && $row = $res->fetch_assoc()) {
                $last = $row['last_login'];
            }
            if (empty($last) && isset($r['last_login'])) {
                $last = $r['last_login'];
            }
            $r['last_login'] = $last;
        }

        return $rows;
    }

    /**
     * Get a single user with enriched fields
     */
    public function getUser($userId) {
        $row = $this->db->getRow('users', '*', 'id = ?', [(int)$userId]);
        if (!$row) return false;
        $row['storage_used'] = $this->getUserStorageUsage($row['id']);
        $res = $this->db->query('SELECT MAX(created_at) AS last_login FROM login_logs WHERE user_id = ?', [$row['id']]);
        if ($res && $r = $res->fetch_assoc()) {
            $row['last_login'] = $r['last_login'];
        }
        return $row;
    }

    /**
     * Search users by username or email
     */
    public function searchUsers($query) {
        $q = '%' . $this->db->escape($query) . '%';
        $rows = $this->db->select('users', '*', 'username LIKE ? OR email LIKE ?', [$q, $q], 'created_at DESC');
        if (!$rows) return [];
        foreach ($rows as &$r) {
            $r['storage_used'] = $this->getUserStorageUsage($r['id']);
        }
        return $rows;
    }

    /**
     * Log an internal action to activity_logs table if present
     */
    public function logAction($message, $type = 'system') {
        $data = [
            'action' => $this->db->escape($message),
            'type' => $this->db->escape($type),
            'performed_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        try {
            return (bool)$this->db->insert('activity_logs', $data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create an impersonation session for admin to switch to a user
     */
    public function createImpersonationSession($userId) {
        $user = $this->getUser($userId);
        if (!$user) return false;
        // store impersonator
        if (isset($_SESSION['user_id'])) {
            $_SESSION['impersonator_id'] = $_SESSION['user_id'];
            $_SESSION['impersonator_username'] = $_SESSION['username'] ?? null;
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['user_type'] ?? 'user';
        $_SESSION['login_time'] = time();
        return true;
    }

    /**
     * Update a user's account status (active, inactive, suspended)
     */
    public function updateUserStatus($userId, $status) {
        $allowed = ['active', 'inactive', 'suspended'];
        if (!in_array($status, $allowed)) {
            return false;
        }

        $userId = (int)$userId;
        return (bool)$this->db->update('users', ['account_status' => $status], 'id = ?', [$userId]);
    }

    /**
     * Get storage usage for a specific user (total bytes)
     */
    public function getUserStorageUsage($userId) {
        $sql = "SELECT COALESCE(SUM(file_size),0) AS total FROM files WHERE user_id = ? AND is_trashed = 0";
        $result = $this->db->query($sql, [$userId]);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total'];
        }
        return 0;
    }

    /**
     * Get overall storage stats and top largest files
     */
    public function getStorageOverview($limitLargest = 10) {
        $overview = ['total_files' => 0, 'total_size' => 0, 'largest_files' => []];

        $result = $this->db->query('SELECT COUNT(*) AS c, COALESCE(SUM(file_size),0) AS s FROM files WHERE is_trashed = 0');
        if ($result && $row = $result->fetch_assoc()) {
            $overview['total_files'] = (int)$row['c'];
            $overview['total_size'] = (int)$row['s'];
        }

        $rows = $this->db->select('files', '*', 'is_trashed = 0', [], 'file_size DESC', $limitLargest);
        $overview['largest_files'] = $rows ?: [];

        return $overview;
    }

    /**
     * Return all files (optionally limited) ordered by newest first
     */
    public function getAllFiles($limit = 0) {
        $order = 'uploaded_at DESC';
        $limitSql = $limit && is_int($limit) && $limit > 0 ? $limit : '';
        $rows = $this->db->select('files', '*', 'is_trashed = 0', [], $order, $limitSql);
        return $rows ?: [];
    }

    /**
     * Backwards-compatible wrapper returning combined system info
     */
    public function getSystemInfo(): array {
        return [
            'stats' => $this->getSystemStats(),
            'health' => $this->getServerHealth(),
            'storage' => $this->getStorageOverview(),
        ];
    }

    /**
     * Return recent backup history if table exists, otherwise empty array
     */
    public function getBackupHistory($limit = 20): array {
        try {
            $rows = $this->db->select('backups', '*', '', [], 'created_at DESC', (int)$limit);
            return $rows ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Return system settings from `settings` table or empty defaults
     */
    public function getSystemSettings(): array {
        try {
            $rows = $this->db->select('settings', '*');
            if (!$rows) return [];
            $out = [];
            foreach ($rows as $r) {
                if (isset($r['name']) && array_key_exists('value', $r)) {
                    $out[$r['name']] = $r['value'];
                } elseif (isset($r['key']) && array_key_exists('value', $r)) {
                    $out[$r['key']] = $r['value'];
                }
            }
            return $out;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Soft-delete a user (mark as deleted, deactivate account)
     */
    public function softDeleteUser($userId) {
        $now = date('Y-m-d H:i:s');
        return (bool)$this->db->update('users', ['is_deleted' => 1, 'account_status' => 'inactive', 'deleted_at' => $now], 'id = ?', [(int)$userId]);
    }

    /**
     * Force logout a user by clearing their session record (if sessions tracked)
     * This implementation assumes sessions are stored in DB table `sessions` with `user_id`.
     */
    public function forceLogoutUser($userId) {
        // If sessions table exists, delete sessions for user
        try {
            $this->db->delete('sessions', 'user_id = ?', [(int)$userId]);
        } catch (Exception $e) {
            // ignore if sessions table isn't present
        }
        // Also remove any server-side flags
        return true;
    }

    /**
     * Get recent login logs for a user (if login_logs table exists)
     */
    public function getLoginLogs($userId, $limit = 50) {
        $rows = $this->db->select('login_logs', '*', 'user_id = ?', [(int)$userId], 'created_at DESC', (int)$limit);
        return $rows ?: [];
    }

    /**
     * Get failed login attempts summary
     */
    public function getFailedLogins($limit = 50) {
        $rows = $this->db->select('failed_logins', '*', '', [], 'attempted_at DESC', (int)$limit);
        return $rows ?: [];
    }

    /**
     * Log an administrative message to a user (store in messages table if present)
     */
    public function sendAdminMessage($userId, $subject, $message) {
        $data = [
            'user_id' => (int)$userId,
            'subject' => $this->db->escape($subject),
            'message' => $this->db->escape($message),
            'created_at' => date('Y-m-d H:i:s')
        ];
        try {
            return (bool)$this->db->insert('messages', $data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Compatibility wrapper used by admin pages
     */
    public function getSystemStatistics() {
        return $this->getSystemStats();
    }

    /**
     * Get recent activities (falls back to available logs tables)
     */
    public function getRecentActivities($limit = 50) {
        $tables = ['activities', 'activity_logs', 'login_logs', 'user_actions'];
        foreach ($tables as $t) {
            $rows = $this->db->select($t, '*', '', [], 'created_at DESC', (int)$limit);
            if ($rows !== false && count($rows) > 0) {
                return $rows;
            }
        }
        return [];
    }

    /**
     * Basic server health summary
     */
    public function getServerHealth() {
        $root = __DIR__ . '/../';
        $total = @disk_total_space($root) ?: 0;
        $free = @disk_free_space($root) ?: 0;
        $used = $total - $free;
        $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0;

        return [
            'disk_total' => $total,
            'disk_free' => $free,
            'disk_used' => $used,
            'disk_used_percent' => $percent,
            'php_version' => phpversion(),
            'os' => PHP_OS,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];
    }

    /**
     * Return system alerts (low disk, many failed logins, etc.)
     */
    public function getSystemAlerts(): array {
        $alerts = [];

        // Disk space alert
        $root = __DIR__ . '/../';
        $total = @disk_total_space($root) ?: 0;
        $free = @disk_free_space($root) ?: 0;
        if ($total > 0) {
            $usedPercent = round((($total - $free) / $total) * 100, 2);
            if ($usedPercent >= 90) {
                $alerts[] = ['level' => 'critical', 'message' => "Disk usage is at {$usedPercent}%"];
            } elseif ($usedPercent >= 75) {
                $alerts[] = ['level' => 'warning', 'message' => "Disk usage is at {$usedPercent}%"];
            }
        }

        // Failed login spike (if table exists)
        try {
            $rows = $this->db->select('failed_logins', 'COUNT(*) as c', 'attempted_at >= ?', [date('Y-m-d H:i:s', strtotime('-24 hours'))]);
            if ($rows !== false && isset($rows[0]['c']) && (int)$rows[0]['c'] > 50) {
                $alerts[] = ['level' => 'warning', 'message' => (int)$rows[0]['c'] . " failed login attempts in the last 24 hours"];
            }
        } catch (Exception $e) {
            // ignore if table missing
        }

        // Recent admin actions requiring attention (if activity logs exist)
        try {
            $recent = $this->db->select('activity_logs', '*', 'created_at >= ?', [date('Y-m-d H:i:s', strtotime('-1 day'))], 'created_at DESC', 5);
            if ($recent && count($recent) > 0) {
                foreach ($recent as $r) {
                    if (strpos(strtolower($r['action'] ?? ''), 'suspend') !== false || strpos(strtolower($r['action'] ?? ''), 'delete') !== false) {
                        $alerts[] = ['level' => 'info', 'message' => ($r['action'] ?? 'Admin action') . ' by ' . ($r['performed_by'] ?? 'system')];
                    }
                }
            }
        } catch (Exception $e) {
            // ignore
        }

        return $alerts;
    }
}
?>