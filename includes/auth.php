<?php
/**
 * Authentication Middleware
 * 125 Gor Kadva Patel Samaj Matrimony
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Require user login - redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        setFlashMessage('warning', 'Please login to continue.');
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit();
    }
}

/**
 * Get current logged-in user data
 */
function getCurrentUser($pdo) {
    if (!isset($_SESSION['user_id'])) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND profile_status != 'deleted'");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        updateLastActive($pdo, $user['id']);
    }
    
    return $user;
}

/**
 * Get current admin data
 */
function getCurrentAdmin($pdo) {
    if (!isset($_SESSION['admin_id'])) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Login user
 */
function loginUser($pdo, $emailOrMobile, $password, $remember = false) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR mobile = ?) AND profile_status != 'deleted'");
    $stmt->execute([$emailOrMobile, $emailOrMobile]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'error' => 'No account found with these credentials.'];
    }
    
    if ($user['profile_status'] === 'blocked') {
        return ['success' => false, 'error' => 'Your account has been blocked. Contact support.'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Invalid password. Please try again.'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_photo'] = $user['profile_photo'];
    
    // Remember me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
        setcookie('user_id', $user['id'], time() + (86400 * 30), '/', '', false, true);
    }
    
    // Update last active
    updateLastActive($pdo, $user['id']);
    
    return ['success' => true, 'user' => $user];
}

/**
 * Login admin
 */
function loginAdmin($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($password, $admin['password'])) {
        return ['success' => false, 'error' => 'Invalid admin credentials.'];
    }
    
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_role'] = $admin['role'];
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$admin['id']]);
    
    return ['success' => true, 'admin' => $admin];
}

/**
 * Logout user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Clear remember me cookies
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');
    }
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Logout admin
 */
function logoutAdmin() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Check remember me cookie and auto-login
 */
function checkRememberMe($pdo) {
    if (!isLoggedIn() && isset($_COOKIE['remember_token']) && isset($_COOKIE['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND remember_token = ? AND profile_status = 'active'");
        $stmt->execute([$_COOKIE['user_id'], $_COOKIE['remember_token']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_photo'] = $user['profile_photo'];
            updateLastActive($pdo, $user['id']);
        }
    }
}

// Auto-check remember me on include
checkRememberMe($pdo);
