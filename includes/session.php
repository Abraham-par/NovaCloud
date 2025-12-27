<?php
require_once 'config.php';

class SessionManager {
    
    public function __construct() {
        // Session already started in config.php
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }
    
    public function isAdmin() {
        return $this->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . SITE_URL . 'auth.php');
            exit();
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        
        if (!$this->isAdmin()) {
            header('Location: ' . SITE_URL . 'errors/403.php');
            exit();
        }
    }
    
    public function login($user_id, $username, $role = 'user') {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $role;
        $_SESSION['login_time'] = time();
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        session_write_close();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }
    
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }
}
?>