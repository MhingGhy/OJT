<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/security.php';

require_login();
set_security_headers();

$trainee_id = $_SESSION['trainee_id'];
$user_id = $_SESSION['user_id'];
$is_forced = isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] === true;

$success = '';
$error = '';
$field_errors = [];

// Check if user has email
$email_check_stmt = mysqli_prepare($conn, "SELECT email FROM trainees WHERE id = ?");
mysqli_stmt_bind_param($email_check_stmt, "i", $trainee_id);
mysqli_stmt_execute($email_check_stmt);
$email_result = mysqli_stmt_get_result($email_check_stmt);
$trainee_data = mysqli_fetch_assoc($email_result);
$needs_email = empty($trainee_data['email']);

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token()) {
        $error = 'Security token validation failed. Please try again.';
        log_security_event('CSRF_FAILURE', 'CSRF token validation failed on password change', 'high');
    } else {
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Email validation (if needed)
    if ($needs_email) {
        if (empty($email)) {
            $field_errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $field_errors['email'] = 'Please enter a valid email address.';
        } else {
            // Check for duplicate email in both trainees and users tables
            $dup_check = mysqli_prepare($conn, "SELECT id FROM trainees WHERE email = ? AND id != ?");
            mysqli_stmt_bind_param($dup_check, "si", $email, $trainee_id);
            mysqli_stmt_execute($dup_check);
            $dup_result = mysqli_stmt_get_result($dup_check);
            
            if (mysqli_num_rows($dup_result) > 0) {
                $field_errors['email'] = 'This email is already in use. Please use a different email.';
            }
        }
    }
    
    // Password validation
    if (empty($current_password)) {
        $field_errors['current_password'] = 'Current password is required.';
    }
    if (empty($new_password)) {
        $field_errors['new_password'] = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $field_errors['new_password'] = 'Password must be at least 8 characters long.';
    }
    if (empty($confirm_password)) {
        $field_errors['confirm_password'] = 'Please confirm your new password.';
    } elseif ($new_password !== $confirm_password) {
        $field_errors['confirm_password'] = 'Passwords do not match.';
    }
    
    if (empty($field_errors)) {
        // Verify current password
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (!verify_password($current_password, $user['password'])) {
            $field_errors['current_password'] = 'Current password is incorrect.';
        } elseif ($current_password === $new_password) {
            $field_errors['new_password'] = 'New password must be different from current password.';
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Update password
                $hashed_password = hash_password($new_password);
                $update_stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, require_password_change = 0 WHERE id = ?");
                mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user_id);
                mysqli_stmt_execute($update_stmt);
                
                // Update email if needed
                if ($needs_email && !empty($email)) {
                    // Update trainee email
                    $email_stmt = mysqli_prepare($conn, "UPDATE trainees SET email = ? WHERE id = ?");
                    mysqli_stmt_bind_param($email_stmt, "si", $email, $trainee_id);
                    mysqli_stmt_execute($email_stmt);
                    
                    // Update user email
                    $user_email_stmt = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE id = ?");
                    mysqli_stmt_bind_param($user_email_stmt, "si", $email, $user_id);
                    mysqli_stmt_execute($user_email_stmt);
                }
                
                // Delete temporary credentials (no longer needed after password change)
                $delete_temp_stmt = mysqli_prepare($conn, "DELETE FROM temporary_credentials WHERE user_id = ?");
                mysqli_stmt_bind_param($delete_temp_stmt, "i", $user_id);
                mysqli_stmt_execute($delete_temp_stmt);
                
                // Commit transaction
                mysqli_commit($conn);
                
                $success = $needs_email ? 'Email and password updated successfully!' : 'Password changed successfully!';
                log_security_event('PASSWORD_CHANGE', "User {$_SESSION['username']} changed password", 'low');
                
                // Clear forced password change flag from session
                if ($is_forced) {
                    unset($_SESSION['force_password_change']);
                    // Redirect to dashboard after a short delay
                    header("refresh:2;url=index.php");
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Failed to update. Please try again.';
                log_security_event('PASSWORD_CHANGE_ERROR', "Failed password change for user {$_SESSION['username']}: " . $e->getMessage(), 'medium');
            }
        }
    } else {
        $error = 'Please correct the errors below.';
    }
}

$page_title = $needs_email ? 'Complete Your Profile' : 'Change Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
    <div class="container-fluid">
        <div class="row">
            <?php if (!$is_forced): ?>
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-4">
                    <h4 class="fw-bold mb-4">
                        <i class="bi bi-clipboard-data text-primary"></i> OJT System
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person"></i> My Profile
                        </a>
                        <a class="nav-link" href="certificates.php">
                            <i class="bi bi-award"></i> Certificates
                        </a>
                        <hr>
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="<?php echo $is_forced ? 'col-12' : 'col-md-9 col-lg-10'; ?> px-4 py-4">
                <div class="<?php echo $is_forced ? 'row justify-content-center' : ''; ?>">
                    <div class="<?php echo $is_forced ? 'col-md-6' : ''; ?>">
                        <?php if ($is_forced): ?>
                            <div class="text-center mb-4">
                                <div class="login-icon mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                                        <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                                        <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>
                                </div>
                                <h2 class="fw-bold mb-2"><?php echo $needs_email ? 'Complete Your Profile' : 'Change Your Password'; ?></h2>
                                <p class="text-muted">
                                    <?php if ($needs_email): ?>
                                        Please provide your email address and set a new password to access the system.
                                    <?php else: ?>
                                        For security reasons, you must change your temporary password before accessing the system.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="fw-bold mb-0">Change Password</h2>
                                <a href="edit_profile.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Profile
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                                <?php if ($is_forced): ?>
                                    <p class="mb-0 mt-2"><small>Redirecting to dashboard...</small></p>
                                <?php else: ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="<?php echo $is_forced ? 'card glass-card shadow-lg' : 'table-card'; ?>">
                            <div class="<?php echo $is_forced ? 'card-body p-5' : ''; ?>">
                                <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock text-primary"></i> <?php echo $needs_email ? 'Complete Your Profile' : 'Update Your Password'; ?></h5>
                                
                                <?php if ($needs_email): ?>
                                    <div class="alert alert-info mb-3" role="alert">
                                        <i class="bi bi-info-circle-fill"></i> 
                                        <strong>Welcome!</strong> Please provide your email address and set a new password to complete your profile.
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <?php echo csrf_token_field(); ?>
                                    <?php if ($needs_email): ?>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address *</label>
                                            <input type="email" class="form-control <?php echo isset($field_errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                            <small class="text-muted">This will be your contact email for the system</small>
                                            <?php if (isset($field_errors['email'])): ?>
                                                <div class="invalid-feedback"><?php echo $field_errors['email']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password *</label>
                                        <input type="password" class="form-control <?php echo isset($field_errors['current_password']) ? 'is-invalid' : ''; ?>" id="current_password" name="current_password" required>
                                        <small class="text-muted">Your temporary password</small>
                                        <?php if (isset($field_errors['current_password'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['current_password']; ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password *</label>
                                        <input type="password" class="form-control <?php echo isset($field_errors['new_password']) ? 'is-invalid' : ''; ?>" id="new_password" name="new_password" minlength="8" required>
                                        <small class="text-muted">Minimum 8 characters</small>
                                        <?php if (isset($field_errors['new_password'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['new_password']; ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                                        <input type="password" class="form-control <?php echo isset($field_errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" minlength="8" required>
                                        <?php if (isset($field_errors['confirm_password'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['confirm_password']; ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg <?php echo $is_forced ? 'w-100' : ''; ?>">
                                        <i class="bi bi-check-circle"></i> <?php echo $needs_email ? 'Complete Profile' : 'Change Password'; ?>
                                    </button>
                                    <?php if (!$is_forced): ?>
                                        <a href="edit_profile.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
