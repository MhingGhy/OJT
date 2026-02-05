<?php
/**
 * Security Utilities
 * OJT Information and Tracking System
 * 
 * This file contains security functions for:
 * - CSRF token generation and validation
 * - Rate limiting and brute force protection
 * - Secure password generation
 * - Security headers
 */

// ============================================================================
// CSRF PROTECTION
// ============================================================================

/**
 * Generate CSRF token
 * Creates a unique token for the current session
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * Checks if the submitted token matches the session token
 * 
 * @param string $token The token to validate
 * @return bool True if valid, false otherwise
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Use hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field
 * Returns HTML for hidden CSRF token field
 * 
 * @return string HTML input field
 */
function csrf_token_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF token from POST request
 * Validates token and shows error if invalid
 * 
 * @return bool True if valid, false otherwise
 */
function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!validate_csrf_token($token)) {
            // Log security event
            error_log('CSRF token validation failed. IP: ' . get_client_ip() . ', User: ' . ($_SESSION['username'] ?? 'guest'));
            return false;
        }
    }
    return true;
}

// ============================================================================
// RATE LIMITING & BRUTE FORCE PROTECTION
// ============================================================================

/**
 * Check if login attempts exceed rate limit
 * 
 * @param string $username Username attempting to login
 * @param string $ip_address IP address of the client
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
 */
function check_rate_limit($username, $ip_address) {
    global $conn;
    
    $max_attempts = 5;
    $lockout_duration = 900; // 15 minutes in seconds
    $time_window = time() - $lockout_duration;
    
    // Count recent failed attempts
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as attempts FROM login_attempts 
                                    WHERE (username = ? OR ip_address = ?) 
                                    AND attempt_time > FROM_UNIXTIME(?) 
                                    AND success = 0");
    mysqli_stmt_bind_param($stmt, "ssi", $username, $ip_address, $time_window);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $attempts = $row['attempts'];
    
    // Check if locked out
    if ($attempts >= $max_attempts) {
        // Get time of first failed attempt in window
        $stmt = mysqli_prepare($conn, "SELECT UNIX_TIMESTAMP(attempt_time) as first_attempt 
                                        FROM login_attempts 
                                        WHERE (username = ? OR ip_address = ?) 
                                        AND attempt_time > FROM_UNIXTIME(?) 
                                        AND success = 0 
                                        ORDER BY attempt_time ASC 
                                        LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ssi", $username, $ip_address, $time_window);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        $reset_time = $row['first_attempt'] + $lockout_duration;
        
        return [
            'allowed' => false,
            'remaining' => 0,
            'reset_time' => $reset_time,
            'wait_minutes' => ceil(($reset_time - time()) / 60)
        ];
    }
    
    return [
        'allowed' => true,
        'remaining' => $max_attempts - $attempts,
        'reset_time' => time() + $lockout_duration
    ];
}

/**
 * Log login attempt
 * 
 * @param string $username Username attempting to login
 * @param string $ip_address IP address of the client
 * @param bool $success Whether the login was successful
 */
function log_login_attempt($username, $ip_address, $success) {
    global $conn;
    
    $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (username, ip_address, attempt_time, success) 
                                    VALUES (?, ?, NOW(), ?)");
    $success_int = $success ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "ssi", $username, $ip_address, $success_int);
    mysqli_stmt_execute($stmt);
    
    // Clean up old attempts (older than 24 hours) - FIXED: Using prepared statement
    $cleanup_stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    mysqli_stmt_execute($cleanup_stmt);
}

/**
 * Clear login attempts for user
 * Called after successful login
 * 
 * @param string $username Username to clear attempts for
 */
function clear_login_attempts($username) {
    global $conn;
    
    $stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
}

// ============================================================================
// SECURE PASSWORD GENERATION
// ============================================================================

/**
 * Generate cryptographically secure random password
 * 
 * @param int $length Length of password (default 12)
 * @return string Generated password
 */
function generate_secure_password($length = 12) {
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $special = '!@#$%^&*()-_=+';
    
    $all_chars = $lowercase . $uppercase . $numbers . $special;
    
    // Ensure at least one character from each set
    $password = '';
    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $special[random_int(0, strlen($special) - 1)];
    
    // Fill the rest randomly
    for ($i = 4; $i < $length; $i++) {
        $password .= $all_chars[random_int(0, strlen($all_chars) - 1)];
    }
    
    // Shuffle the password
    $password = str_shuffle($password);
    
    return $password;
}

// ============================================================================
// SECURITY HEADERS
// ============================================================================

/**
 * Set security headers
 * Should be called at the beginning of each page
 */
function set_security_headers() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' cdn.jsdelivr.net; connect-src 'self'");
    
    // Permissions policy (disable unnecessary features)
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // XSS Protection (legacy, but doesn't hurt)
    header('X-XSS-Protection: 1; mode=block');
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Get client IP address
 * Handles proxies and load balancers
 * 
 * @return string Client IP address
 */
function get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Sanitize output for HTML display
 * Use this when displaying user input in HTML
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Log security event
 * 
 * @param string $event_type Type of security event
 * @param string $description Description of the event
 * @param string $severity Severity level (low, medium, high, critical)
 */
function log_security_event($event_type, $description, $severity = 'medium') {
    $log_file = __DIR__ . '/../logs/security.log';
    $log_dir = dirname($log_file);
    
    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = get_client_ip();
    $user = $_SESSION['username'] ?? 'guest';
    
    $log_entry = sprintf(
        "[%s] [%s] [%s] IP: %s, User: %s - %s: %s\n",
        $timestamp,
        strtoupper($severity),
        $event_type,
        $ip,
        $user,
        $event_type,
        $description
    );
    
    error_log($log_entry, 3, $log_file);
}
?>
