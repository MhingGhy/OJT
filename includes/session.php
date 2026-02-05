<?php
/**
 * Session Management
 * OJT Information and Tracking System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Enhanced session security configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 when using HTTPS
    ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
    ini_set('session.name', 'OJT_SESSION'); // Custom session name
    ini_set('session.use_strict_mode', 1); // Prevent session fixation
    session_start();
}

// Session timeout (30 minutes)
$timeout_duration = 1800;

// Check if session has timed out
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?timeout=1");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

/**
 * Set login session
 */
function set_login_session($user_data) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['role'] = $user_data['role'];
    $_SESSION['trainee_id'] = $user_data['trainee_id'];
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Require login - redirect to login page if not logged in
 * Also enforces password change requirement
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: ../login.php");
        exit();
    }
    
    // Check if password change is required (except on change_password.php)
    if (isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] === true) {
        $current_page = basename($_SERVER['PHP_SELF']);
        if ($current_page !== 'change_password.php') {
            header("Location: change_password.php");
            exit();
        }
    }
}

/**
 * Require admin - redirect to login page if not admin
 */
function require_admin() {
    if (!is_logged_in()) {
        header("Location: ../login.php");
        exit();
    }
    
    if (!is_admin()) {
        header("Location: ../user/index.php");
        exit();
    }
}

/**
 * Destroy session
 */
function destroy_session() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
?>
