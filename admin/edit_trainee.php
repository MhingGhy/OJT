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

// Get trainee information
$stmt = mysqli_prepare($conn, "SELECT * FROM trainees WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);
mysqli_stmt_execute($stmt);
$trainee = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$trainee) {
    redirect('trainees.php');
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $middle_name = sanitize_input($_POST['middle_name']);
    $age = (int)$_POST['age'];
    $sex = sanitize_input($_POST['sex']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $course = sanitize_input($_POST['course']);
    $school = sanitize_input($_POST['school']);
    $year_level = sanitize_input($_POST['year_level']);
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($course) || empty($school)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Update trainee
        $stmt = mysqli_prepare($conn, "UPDATE trainees SET first_name = ?, last_name = ?, middle_name = ?, age = ?, sex = ?, email = ?, phone = ?, address = ?, course = ?, school = ?, year_level = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssisssssssi", $first_name, $last_name, $middle_name, $age, $sex, $email, $phone, $address, $course, $school, $year_level, $trainee_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Trainee updated successfully!';
            // Refresh trainee data
            $stmt = mysqli_prepare($conn, "SELECT * FROM trainees WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $trainee_id);
            mysqli_stmt_execute($stmt);
            $trainee = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        } else {
            $error = 'Error updating trainee. Please try again.';
        }
    }
}

$page_title = 'Edit Trainee';
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
                    <h2 class="fw-bold mb-0">Edit Trainee</h2>
                    <a href="trainees.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>

                <?php if ($success): ?>
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

                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Personal Information -->
                        <div class="col-12">
                            <div class="table-card">
                                <h5 class="fw-bold mb-3">Personal Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($trainee['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="middle_name" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($trainee['middle_name'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($trainee['last_name']); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="age" class="form-label">Age *</label>
                                        <input type="number" class="form-control" id="age" name="age" min="16" max="100" value="<?php echo $trainee['age']; ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="sex" class="form-label">Sex *</label>
                                        <select class="form-select" id="sex" name="sex" required>
                                            <option value="Male" <?php echo $trainee['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo $trainee['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($trainee['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($trainee['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($trainee['address'] ?? ''); ?></textarea>
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
                                        <input type="text" class="form-control" id="course" name="course" value="<?php echo htmlspecialchars($trainee['course']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="school" class="form-label">School *</label>
                                        <input type="text" class="form-control" id="school" name="school" value="<?php echo htmlspecialchars($trainee['school']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="year_level" class="form-label">Year Level</label>
                                        <input type="text" class="form-control" id="year_level" name="year_level" value="<?php echo htmlspecialchars($trainee['year_level'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Update Trainee
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
</body>
</html>
