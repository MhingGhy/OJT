<?php
/**
 * Database Migration Runner
 * Run this script once to create the login_attempts table
 * Access via: http://localhost/ojt-system/database/run_migration.php
 */

// Include database connection
require_once '../includes/config.php';

// Check if already run
$check = mysqli_query($conn, "SHOW TABLES LIKE 'login_attempts'");
if (mysqli_num_rows($check) > 0) {
    die('✅ Migration already completed. The login_attempts table already exists.');
}

// Read the SQL file
$sql_file = __DIR__ . '/create_login_attempts_table.sql';
if (!file_exists($sql_file)) {
    die('❌ Error: Migration file not found at ' . $sql_file);
}

$sql = file_get_contents($sql_file);

// Execute the SQL
if (mysqli_multi_query($conn, $sql)) {
    // Wait for all queries to complete
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    
    echo '✅ Migration completed successfully!<br>';
    echo 'The login_attempts table has been created.<br><br>';
    echo '<a href="../login.php">Go to Login Page</a>';
} else {
    echo '❌ Error running migration: ' . mysqli_error($conn);
}

mysqli_close($conn);
?>
