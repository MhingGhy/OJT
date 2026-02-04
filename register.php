<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

// Public registration is disabled - only admins can create trainee accounts
// Redirect to login page with message
session_start();
$_SESSION['registration_disabled_message'] = 'Public registration is currently disabled. Please contact an administrator to create your account.';
redirect('login.php');
exit();

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/index.php');
    } else {
        redirect('user/index.php');
    }
}

$success = '';
$error = '';
$generated_username = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Personal Information
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $middle_name = sanitize_input($_POST['middle_name']);
    $age = intval($_POST['age']);
    $sex = sanitize_input($_POST['sex']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    
    // Educational Information
    $course = sanitize_input($_POST['course']);
    $school = sanitize_input($_POST['school']);
    $year_level = sanitize_input($_POST['year_level']);
    
    // Password
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($age) || empty($sex) || empty($course) || empty($school) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Check if email already exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM trainees WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email address already registered.';
        } else {
            // Generate username from name (firstname.lastname)
            $username = strtolower($first_name . '.' . $last_name);
            $username = preg_replace('/[^a-z0-9.]/', '', $username); // Remove special characters
            
            // Check if username exists, add number if needed
            $original_username = $username;
            $counter = 1;
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while (mysqli_num_rows($result) > 0) {
                $username = $original_username . $counter;
                $counter++;
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            }
            
            // Hash the user's password
            $hashed_password = hash_password($password);
            
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Insert trainee
                $stmt = mysqli_prepare($conn, "INSERT INTO trainees (first_name, last_name, middle_name, age, sex, email, phone, address, course, school, year_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssisssssss", $first_name, $last_name, $middle_name, $age, $sex, $email, $phone, $address, $course, $school, $year_level);
                mysqli_stmt_execute($stmt);
                $trainee_id = mysqli_insert_id($conn);
                
                // Insert user account
                $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, email, role, trainee_id) VALUES (?, ?, ?, 'user', ?)");
                mysqli_stmt_bind_param($stmt, "sssi", $username, $hashed_password, $email, $trainee_id);
                mysqli_stmt_execute($stmt);
                
                // Commit transaction
                mysqli_commit($conn);
                
                $success = 'Registration successful!';
                $generated_username = $username;
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="register-page">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card glass-card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="login-icon mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16">
                                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                    <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                                </svg>
                            </div>
                            <h2 class="fw-bold mb-2">Create Your Account</h2>
                            <p class="text-muted">Register as a trainee to access the OJT tracking system</p>
                        </div>

                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <h5 class="alert-heading"><i class="bi bi-check-circle"></i> <?php echo $success; ?></h5>
                                <hr>
                                <p class="mb-2"><strong>Your Username:</strong> <code><?php echo $generated_username; ?></code></p>
                                <p class="mb-0"><small><i class="bi bi-info-circle"></i> Please save your username. Use it with the password you created to login.</small></p>
                                <div class="mt-3">
                                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" id="registerForm">
                                <h5 class="text-navy mb-3"><i class="bi bi-person"></i> Personal Information</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="middle_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="middle_name" name="middle_name">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="age" name="age" min="16" max="100" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                                        <select class="form-control" id="sex" name="sex" required>
                                            <option value="">Select...</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="09XXXXXXXXX">
                                </div>

                                <div class="mb-4">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                                </div>

                                <h5 class="text-navy mb-3"><i class="bi bi-mortarboard"></i> Educational Information</h5>

                                <div class="mb-3">
                                    <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="course" name="course" placeholder="e.g., Bachelor of Science in Information Technology" required>
                                </div>

                                <div class="mb-3">
                                    <label for="school" class="form-label">School <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="school" name="school" placeholder="e.g., University of the Philippines" required>
                                </div>

                                <div class="mb-4">
                                    <label for="year_level" class="form-label">Year Level</label>
                                    <select class="form-control" id="year_level" name="year_level">
                                        <option value="">Select...</option>
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                        <option value="5th Year">5th Year</option>
                                    </select>
                                </div>

                                <h5 class="text-navy mb-3"><i class="bi bi-key"></i> Create Password</h5>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="bi bi-person-plus"></i> Create Account
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <a href="index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Back to Home
                            </a>
                            <span class="mx-2">|</span>
                            <a href="login.php" class="text-decoration-none">Already have an account? Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
