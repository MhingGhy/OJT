<?php
/**
 * Database Configuration File
 * OJT Information and Tracking System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ojt_system');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Site configuration
define('SITE_NAME', 'OJT Tracking System');
define('SITE_URL', 'http://localhost/ojt-system');
define('UPLOAD_PATH', __DIR__ . '/../uploads/certificates/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes

// Timezone
date_default_timezone_set('Asia/Manila');

// Security Configuration
define('ENVIRONMENT', 'development'); // Set to 'production' for live deployment
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_DURATION', 900); // 15 minutes in seconds
define('ENABLE_SECURITY_LOGGING', true);

// Error Reporting (hide in production)
if (ENVIRONMENT === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Create logs directory if it doesn't exist
$logs_dir = __DIR__ . '/../logs';
if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}
?>
