<?php
/**
 * Password Update Script
 * This script updates the password hashes in the database to match the documented credentials
 * Run this once after importing the database
 */

require_once '../includes/config.php';

// Generate correct password hashes
$admin_password = 'admin123';
$user_password = 'password123';

$admin_hash = password_hash($admin_password, PASSWORD_BCRYPT);
$user_hash = password_hash($user_password, PASSWORD_BCRYPT);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Password Update Script</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link rel='stylesheet' href='../assets/css/style.css'>";
echo "</head>";
echo "<body class='bg-light'>";
echo "<div class='container py-5'>";
echo "<div class='row justify-content-center'>";
echo "<div class='col-md-8'>";
echo "<div class='card shadow'>";
echo "<div class='card-header bg-navy text-white'>";
echo "<h3 class='mb-0'><i class='bi bi-key'></i> Password Update Script</h3>";
echo "</div>";
echo "<div class='card-body'>";

try {
    // Update admin password
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = 'admin'");
    mysqli_stmt_bind_param($stmt, "s", $admin_hash);
    $admin_updated = mysqli_stmt_execute($stmt);
    
    // Update all trainee passwords
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE role = 'user'");
    mysqli_stmt_bind_param($stmt, "s", $user_hash);
    $users_updated = mysqli_stmt_execute($stmt);
    
    if ($admin_updated && $users_updated) {
        echo "<div class='alert alert-success'>";
        echo "<h5><i class='bi bi-check-circle'></i> Success!</h5>";
        echo "<p>Password hashes have been updated successfully.</p>";
        echo "<hr>";
        echo "<h6>Updated Credentials:</h6>";
        echo "<ul>";
        echo "<li><strong>Admin:</strong> username = <code>admin</code>, password = <code>admin123</code></li>";
        echo "<li><strong>All Trainees:</strong> password = <code>password123</code></li>";
        echo "</ul>";
        echo "<p class='mb-0'><small>Sample trainee usernames: juan.delacruz, maria.garcia, pedro.santos, ana.mendoza</small></p>";
        echo "</div>";
        
        echo "<div class='alert alert-info'>";
        echo "<h6><i class='bi bi-info-circle'></i> Next Steps:</h6>";
        echo "<ol>";
        echo "<li>Test the login credentials at <a href='../login.php'>login page</a></li>";
        echo "<li>For admin access, use: <a href='../login.php?admin=1'>admin login</a></li>";
        echo "<li>Delete or rename this file for security</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<div class='d-grid gap-2'>";
        echo "<a href='../login.php' class='btn btn-primary'>Go to Login Page</a>";
        echo "<a href='../login.php?admin=1' class='btn btn-secondary'>Go to Admin Login</a>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<h5><i class='bi bi-x-circle'></i> Error!</h5>";
        echo "<p>Failed to update passwords. Please check your database connection.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5><i class='bi bi-x-circle'></i> Error!</h5>";
    echo "<p>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</body>";
echo "</html>";

mysqli_close($conn);
?>
