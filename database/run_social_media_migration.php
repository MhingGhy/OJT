<?php
/**
 * Migration: Add Social Media Columns to Trainees Table
 * This adds linkedin_url, github_url, and portfolio_url columns
 */

require_once '../includes/config.php';

// Check if columns already exist
$check_query = "SHOW COLUMNS FROM trainees LIKE 'linkedin_url'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    die("Migration already applied. Social media columns already exist.");
}

// Add the columns
$sql = "ALTER TABLE trainees 
        ADD COLUMN linkedin_url VARCHAR(255) DEFAULT NULL AFTER bio,
        ADD COLUMN github_url VARCHAR(255) DEFAULT NULL AFTER linkedin_url,
        ADD COLUMN portfolio_url VARCHAR(255) DEFAULT NULL AFTER github_url";

if (mysqli_query($conn, $sql)) {
    echo "✅ SUCCESS! Social media columns added to trainees table.<br>";
    echo "- linkedin_url<br>";
    echo "- github_url<br>";
    echo "- portfolio_url<br>";
    echo "<br>You can now add social media links to trainee profiles!";
} else {
    echo "❌ ERROR: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
