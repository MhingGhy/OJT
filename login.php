<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/index.php');
    } else {
        redirect('user/index.php');
    }
}

$error = '';
$info_message = '';
$is_admin_login = isset($_GET['admin']);

// Check for registration disabled message
if (isset($_SESSION['registration_disabled_message'])) {
    $info_message = $_SESSION['registration_disabled_message'];
    unset($_SESSION['registration_disabled_message']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Verify password
            if (verify_password($password, $user['password'])) {
                // Update last login
                $update_stmt = mysqli_prepare($conn, "UPDATE users SET last_login = NOW() WHERE id = ?");
                mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                mysqli_stmt_execute($update_stmt);
                
                // Set session
                set_login_session($user);
                
                // Check if password change is required
                if (isset($user['require_password_change']) && $user['require_password_change'] == 1) {
                    $_SESSION['force_password_change'] = true;
                    redirect('user/change_password.php');
                }
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('user/index.php');
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

// Check for timeout
if (isset($_GET['timeout'])) {
    $error = 'Your session has expired. Please login again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_admin_login ? 'Admin' : 'Trainee'; ?> Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card glass-card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="login-icon mb-3">
                                <?php if ($is_admin_login): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-shield-lock" viewBox="0 0 16 16">
                                        <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                                        <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z"/>
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                        <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <h2 class="fw-bold mb-2"><?php echo $is_admin_login ? 'Admin' : 'Trainee'; ?> Login</h2>
                            <p class="text-muted">Enter your credentials to continue</p>
                        </div>

                        <?php if ($info_message): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle"></i> <?php echo $info_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Back to Home
                            </a>
                            <?php if ($is_admin_login): ?>
                                <span class="mx-2">|</span>
                                <a href="login.php" class="text-decoration-none">Trainee Login</a>
                            <?php else: ?>
                                <span class="mx-2">|</span>
                                <a href="login.php?admin=1" class="text-decoration-none">Admin Login</a>
                            <?php endif; ?>
                        </div>

                        <?php if (!$is_admin_login): ?>
                            <div class="mt-4 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <strong>Demo Credentials:</strong><br>
                                    Username: juan.delacruz<br>
                                    Password: password123
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="mt-4 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <strong>Admin Credentials:</strong><br>
                                    Username: admin<br>
                                    Password: admin123
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
