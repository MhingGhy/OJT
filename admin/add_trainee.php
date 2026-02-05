<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/security.php';

require_admin();
set_security_headers();

$success = '';
$error = '';
$field_errors = []; // Store field-specific errors
$form_data = []; // Store form values for repopulation
$generated_credentials = null; // Store generated credentials

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token()) {
        $error = 'Security token validation failed. Please try again.';
        log_security_event('CSRF_FAILURE', 'CSRF token validation failed on add trainee', 'high');
    } else {
    // Capture all form data for repopulation
    $form_data = [
        'first_name' => sanitize_input($_POST['first_name'] ?? ''),
        'last_name' => sanitize_input($_POST['last_name'] ?? ''),
        'middle_name' => sanitize_input($_POST['middle_name'] ?? ''),
        'age' => $_POST['age'] ?? '',
        'sex' => sanitize_input($_POST['sex'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'phone' => sanitize_input($_POST['phone'] ?? ''),
        'address' => sanitize_input($_POST['address'] ?? ''),
        'course' => sanitize_input($_POST['course'] ?? ''),
        'school' => sanitize_input($_POST['school'] ?? ''),
        'year_level' => sanitize_input($_POST['year_level'] ?? ''),
        'training_type' => sanitize_input($_POST['training_type'] ?? ''),
        'start_date' => sanitize_input($_POST['start_date'] ?? ''),
        'end_date' => sanitize_input($_POST['end_date'] ?? ''),
        'department' => sanitize_input($_POST['department'] ?? ''),
        'supervisor_name' => sanitize_input($_POST['supervisor_name'] ?? '')
    ];
    
    $age = (int)$form_data['age'];
    
    // Field-specific validation
    if (empty($form_data['first_name'])) {
        $field_errors['first_name'] = 'First name is required.';
    }
    if (empty($form_data['last_name'])) {
        $field_errors['last_name'] = 'Last name is required.';
    }
    // Email is optional, but validate format if provided
    if (!empty($form_data['email']) && !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Please enter a valid email address.';
    }
    if (empty($form_data['course'])) {
        $field_errors['course'] = 'Course is required.';
    }
    if (empty($form_data['school'])) {
        $field_errors['school'] = 'School is required.';
    }
    if (empty($age) || $age < 16 || $age > 100) {
        $field_errors['age'] = 'Please enter a valid age (16-100).';
    }
    if (empty($form_data['sex'])) {
        $field_errors['sex'] = 'Sex is required.';
    }
    
    // If no field errors, proceed with database operations
    if (empty($field_errors)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Prepare email value (NULL if empty)
            $email_value = !empty($form_data['email']) ? $form_data['email'] : null;
            
            // Insert trainee
            $stmt = mysqli_prepare($conn, "INSERT INTO trainees (first_name, last_name, middle_name, age, sex, email, phone, address, course, school, year_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssisssssss", 
                    $form_data['first_name'], 
                    $form_data['last_name'], 
                    $form_data['middle_name'], 
                    $age, 
                    $form_data['sex'], 
                    $email_value, 
                    $form_data['phone'], 
                    $form_data['address'], 
                    $form_data['course'], 
                    $form_data['school'], 
                    $form_data['year_level']
                );
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception('Failed to insert trainee');
                }
                
                $trainee_id = mysqli_insert_id($conn);
                
                // Generate temporary credentials
                $username = strtolower($form_data['first_name'] . '.' . $form_data['last_name']);
                $username = preg_replace('/[^a-z0-9.]/', '', $username); // Remove special characters
                
                // Check if username exists, add number if needed
                $original_username = $username;
                $counter = 1;
                $check_user_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
                mysqli_stmt_bind_param($check_user_stmt, "s", $username);
                mysqli_stmt_execute($check_user_stmt);
                $user_result = mysqli_stmt_get_result($check_user_stmt);
                
                while (mysqli_num_rows($user_result) > 0) {
                    $username = $original_username . $counter;
                    $counter++;
                    mysqli_stmt_bind_param($check_user_stmt, "s", $username);
                    mysqli_stmt_execute($check_user_stmt);
                    $user_result = mysqli_stmt_get_result($check_user_stmt);
                }
                
                // Generate temporary password
                $temp_password = generate_password(12); // 12 character password
                $hashed_password = hash_password($temp_password);
                
                // Prepare email value (NULL if empty)
                $email_value = !empty($form_data['email']) ? $form_data['email'] : null;
                
                // Create user account with password change requirement
                $user_stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, email, role, trainee_id, require_password_change) VALUES (?, ?, ?, 'user', ?, 1)");
                mysqli_stmt_bind_param($user_stmt, "sssi", $username, $hashed_password, $email_value, $trainee_id);
                
                if (!mysqli_stmt_execute($user_stmt)) {
                    throw new Exception('Failed to create user account');
                }
                
                $user_id = mysqli_insert_id($conn);
                
                // Insert training record if provided
                if (!empty($form_data['training_type']) && !empty($form_data['start_date']) && !empty($form_data['end_date'])) {
                    $training_stmt = mysqli_prepare($conn, "INSERT INTO training_records (trainee_id, training_type, start_date, end_date, department, supervisor_name, status) VALUES (?, ?, ?, ?, ?, ?, 'Ongoing')");
                    mysqli_stmt_bind_param($training_stmt, "isssss", 
                        $trainee_id, 
                        $form_data['training_type'], 
                        $form_data['start_date'], 
                        $form_data['end_date'], 
                        $form_data['department'], 
                        $form_data['supervisor_name']
                    );
                    mysqli_stmt_execute($training_stmt);
                }
                
                // Store temporary credentials for admin reference
                $temp_cred_stmt = mysqli_prepare($conn, "INSERT INTO temporary_credentials (user_id, trainee_id, temp_username, temp_password) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($temp_cred_stmt, "iiss", $user_id, $trainee_id, $username, $temp_password);
                
                if (!mysqli_stmt_execute($temp_cred_stmt)) {
                    throw new Exception('Failed to store temporary credentials');
                }
                
                // Commit transaction
                mysqli_commit($conn);
                
                // Store credentials for display
                $generated_credentials = [
                    'username' => $username,
                    'password' => $temp_password,
                    'trainee_name' => $form_data['first_name'] . ' ' . $form_data['last_name']
                ];
                
                $success = 'Trainee added successfully!';
                log_security_event('TRAINEE_CREATED', "New trainee created: {$form_data['first_name']} {$form_data['last_name']} (ID: {$trainee_id})", 'low');
                
                // Clear form data after successful submission
                $form_data = [];
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Error adding trainee: ' . $e->getMessage() . '. If this mentions "temporary_credentials", please run the database migration first.';
                log_security_event('TRAINEE_CREATE_ERROR', "Failed to create trainee: " . $e->getMessage(), 'medium');
            }
        } else {
            $error = 'Please correct the errors below and try again.';
        }
    }

$page_title = 'Add New Trainee';
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
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-4">
                    <h4 class="fw-bold mb-4">
                        <i class="bi bi-shield-lock text-primary"></i> Admin Panel
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="trainees.php">
                            <i class="bi bi-people"></i> Manage Trainees
                        </a>
                        <a class="nav-link" href="search.php">
                            <i class="bi bi-search"></i> Search Trainees
                        </a>
                        <hr>
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">Add New Trainee</h2>
                    <a href="trainees.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>

                <?php if ($generated_credentials): ?>
                    <div class="alert alert-success border-success" role="alert">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="alert-heading mb-0"><i class="bi bi-check-circle-fill"></i> <?php echo $success; ?></h5>
                            <a href="add_trainee.php" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-plus-circle"></i> Add Another Trainee
                            </a>
                        </div>
                        <hr>
                        <div class="bg-white p-3 rounded border">
                            <h6 class="fw-bold mb-3">Temporary Login Credentials for <?php echo htmlspecialchars($generated_credentials['trainee_name']); ?></h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Username</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="temp-username" value="<?php echo htmlspecialchars($generated_credentials['username']); ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('temp-username')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Temporary Password</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="temp-password" value="<?php echo htmlspecialchars($generated_credentials['password']); ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('temp-password')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-3 mb-0" role="alert">
                                <small>
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    <strong>Important:</strong> Please save these credentials and share them securely with the trainee. 
                                    The trainee will be required to change their password upon first login.
                                    This information will not be shown again.
                                </small>
                            </div>
                        </div>
                    </div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="addTraineeForm">
                    <?php echo csrf_token_field(); ?>
                    <div class="row g-4">
                        <!-- Personal Information -->
                        <div class="col-12">
                            <div class="table-card">
                                <h5 class="fw-bold mb-3">Personal Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control <?php echo isset($field_errors['first_name']) ? 'is-invalid' : ''; ?>" id="first_name" name="first_name" value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>" required>
                                        <?php if (isset($field_errors['first_name'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['first_name']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="middle_name" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($form_data['middle_name'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control <?php echo isset($field_errors['last_name']) ? 'is-invalid' : ''; ?>" id="last_name" name="last_name" value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>" required>
                                        <?php if (isset($field_errors['last_name'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['last_name']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="age" class="form-label">Age *</label>
                                        <input type="number" class="form-control <?php echo isset($field_errors['age']) ? 'is-invalid' : ''; ?>" id="age" name="age" min="16" max="100" value="<?php echo htmlspecialchars($form_data['age'] ?? ''); ?>" required>
                                        <?php if (isset($field_errors['age'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['age']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="sex" class="form-label">Sex *</label>
                                        <select class="form-select <?php echo isset($field_errors['sex']) ? 'is-invalid' : ''; ?>" id="sex" name="sex" required>
                                            <option value="">Select...</option>
                                            <option value="Male" <?php echo (isset($form_data['sex']) && $form_data['sex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (isset($form_data['sex']) && $form_data['sex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                        <?php if (isset($field_errors['sex'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['sex']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <small class="text-muted">(Optional)</small></label>
                                        <input type="email" class="form-control <?php echo isset($field_errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                                        <?php if (isset($field_errors['email'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['email']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Educational Information -->
                        <div class="col-12">
                            <div class="table-card">
                                <h5 class="fw-bold mb-3">Educational Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="course" class="form-label">Course *</label>
                                        <input type="text" class="form-control <?php echo isset($field_errors['course']) ? 'is-invalid' : ''; ?>" id="course" name="course" value="<?php echo htmlspecialchars($form_data['course'] ?? ''); ?>" required>
                                        <?php if (isset($field_errors['course'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['course']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="school" class="form-label">School *</label>
                                        <input type="text" class="form-control <?php echo isset($field_errors['school']) ? 'is-invalid' : ''; ?>" id="school" name="school" value="<?php echo htmlspecialchars($form_data['school'] ?? ''); ?>" required>
                                        <?php if (isset($field_errors['school'])): ?>
                                            <div class="invalid-feedback"><?php echo $field_errors['school']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="year_level" class="form-label">Year Level</label>
                                        <input type="text" class="form-control" id="year_level" name="year_level" value="<?php echo htmlspecialchars($form_data['year_level'] ?? ''); ?>" placeholder="e.g., 4th Year">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Training Information (Optional) -->
                        <div class="col-12">
                            <div class="table-card">
                                <h5 class="fw-bold mb-3">Training Information (Optional)</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="training_type" class="form-label">Training Type</label>
                                        <input type="text" class="form-control" id="training_type" name="training_type" value="<?php echo htmlspecialchars($form_data['training_type'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control" id="department" name="department" value="<?php echo htmlspecialchars($form_data['department'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($form_data['start_date'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($form_data['end_date'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="supervisor_name" class="form-label">Supervisor Name</label>
                                        <input type="text" class="form-control" id="supervisor_name" name="supervisor_name" value="<?php echo htmlspecialchars($form_data['supervisor_name'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle"></i> Add Trainee
                            </button>
                            <a href="trainees.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function copyToClipboard(elementId) {
            const input = document.getElementById(elementId);
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            
            navigator.clipboard.writeText(input.value).then(function() {
                // Show success feedback
                const button = input.nextElementSibling;
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check"></i>';
                button.classList.add('btn-success');
                button.classList.remove('btn-outline-secondary');
                
                setTimeout(function() {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }, 2000);
            }).catch(function(err) {
                alert('Failed to copy: ' + err);
            });
        }

        // Handle form submission to prevent button stuck state
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addTraineeForm');
            const submitButton = form.querySelector('button[type="submit"]');
            
            if (form && submitButton) {
                form.addEventListener('submit', function(e) {
                    // Disable button and show processing state
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
                    
                    // Re-enable button after a short delay to handle validation errors
                    // This ensures the button becomes clickable again if there are errors
                    setTimeout(function() {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="bi bi-plus-circle"></i> Add Trainee';
                    }, 3000);
                });
                
                // If there are validation errors on page load, ensure button is enabled
                const hasErrors = document.querySelector('.is-invalid');
                if (hasErrors) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="bi bi-plus-circle"></i> Add Trainee';
                }
            }
        });
    </script>
</body>
</html>
