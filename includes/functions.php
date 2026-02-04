<?php
/**
 * Helper Functions
 * OJT Information and Tracking System
 */

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return mysqli_real_escape_string($conn, $data);
}

/**
 * Hash password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to page
 */
function redirect($page) {
    header("Location: " . $page);
    exit();
}

/**
 * Format date
 */
function format_date($date) {
    return date('F d, Y', strtotime($date));
}

/**
 * Format datetime
 */
function format_datetime($datetime) {
    return date('F d, Y g:i A', strtotime($datetime));
}

/**
 * Calculate age from birthdate
 */
function calculate_age($birthdate) {
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    return $birth->diff($today)->y;
}

/**
 * Get file extension
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $allowed_types = ['pdf', 'jpg', 'jpeg', 'png']) {
    $errors = [];
    
    // Check if file was uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error.";
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = "File size exceeds maximum limit of 5MB.";
    }
    
    // Check file extension
    $ext = get_file_extension($file['name']);
    if (!in_array($ext, $allowed_types)) {
        $errors[] = "Invalid file type. Allowed types: " . implode(', ', $allowed_types);
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];
    
    if (!in_array($mime, $allowed_mimes)) {
        $errors[] = "Invalid file format.";
    }
    
    return $errors;
}

/**
 * Generate unique filename
 */
function generate_unique_filename($original_name) {
    $ext = get_file_extension($original_name);
    return uniqid('cert_', true) . '.' . $ext;
}

/**
 * Get trainee full name
 */
function get_trainee_name($trainee_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT first_name, last_name FROM trainees WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $trainee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['first_name'] . ' ' . $row['last_name'];
    }
    return 'Unknown';
}

/**
 * Get training status badge color
 */
function get_status_badge($status) {
    switch ($status) {
        case 'Completed':
            return 'success';
        case 'Ongoing':
            return 'primary';
        case 'Cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Count total trainees
 */
function count_total_trainees() {
    global $conn;
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM trainees");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

/**
 * Count trainees by gender
 */
function count_trainees_by_gender($gender) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM trainees WHERE sex = ?");
    mysqli_stmt_bind_param($stmt, "s", $gender);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

/**
 * Count training records by status
 */
function count_training_by_status($status) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM training_records WHERE status = ?");
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

/**
 * Get recent trainees
 */
function get_recent_trainees($limit = 5) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM trainees ORDER BY created_at DESC LIMIT ?");
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

/**
 * Generate random password
 */
function generate_password($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Log activity
 */
function log_activity($user_id, $action, $description) {
    global $conn;
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $action, $description);
    mysqli_stmt_execute($stmt);
}
?>
