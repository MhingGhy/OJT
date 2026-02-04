<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

require_admin();

// Get trainee ID
if (!isset($_GET['id'])) {
    redirect('trainees.php');
}

$trainee_id = (int)$_GET['id'];

// Delete trainee
$stmt = mysqli_prepare($conn, "DELETE FROM trainees WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);

if (mysqli_stmt_execute($stmt)) {
    // Also delete associated user account
    $user_stmt = mysqli_prepare($conn, "DELETE FROM users WHERE trainee_id = ?");
    mysqli_stmt_bind_param($user_stmt, "i", $trainee_id);
    mysqli_stmt_execute($user_stmt);
    
    $_SESSION['success_message'] = 'Trainee deleted successfully.';
} else {
    $_SESSION['error_message'] = 'Error deleting trainee.';
}

redirect('trainees.php');
?>
