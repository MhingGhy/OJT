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
?>
