<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

require_admin();

// Get trainee ID
if (!isset($_GET['trainee_id'])) {
    redirect('trainees.php');
}

$trainee_id = (int)$_GET['trainee_id'];

// Get trainee information
$stmt = mysqli_prepare($conn, "SELECT * FROM trainees WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);
mysqli_stmt_execute($stmt);
$trainee = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$trainee) {
    redirect('trainees.php');
}

// Get training records for this trainee
$stmt = mysqli_prepare($conn, "SELECT * FROM training_records WHERE trainee_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);
mysqli_stmt_execute($stmt);
$training_records = mysqli_stmt_get_result($stmt);

$success = '';
$error = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certificate_name = sanitize_input($_POST['certificate_name']);
    $training_record_id = (int)$_POST['training_record_id'];
    $description = sanitize_input($_POST['description']);
    
    if (empty($certificate_name)) {
        $error = 'Please enter a certificate name.';
    } elseif (!isset($_FILES['certificate_file']) || $_FILES['certificate_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a file to upload.';
    } else {
        // Validate file
        $file_errors = validate_file_upload($_FILES['certificate_file']);
        
        if (!empty($file_errors)) {
            $error = implode('<br>', $file_errors);
        } else {
            // Generate unique filename
            $original_name = $_FILES['certificate_file']['name'];
            $unique_filename = generate_unique_filename($original_name);
            $upload_path = UPLOAD_PATH . $unique_filename;
            
            // Create upload directory if it doesn't exist
            if (!file_exists(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $upload_path)) {
                // Insert certificate record
                $file_type = get_file_extension($original_name);
                $file_size = $_FILES['certificate_file']['size'];
                $file_path = '../uploads/certificates/' . $unique_filename;
                
                $stmt = mysqli_prepare($conn, "INSERT INTO certificates (training_record_id, trainee_id, certificate_name, file_name, file_path, file_type, file_size, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iissssss", $training_record_id, $trainee_id, $certificate_name, $unique_filename, $file_path, $file_type, $file_size, $description);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = 'Certificate uploaded successfully!';
                } else {
                    $error = 'Error saving certificate record.';
                    unlink($upload_path); // Delete uploaded file
                }
            } else {
                $error = 'Error uploading file. Please try again.';
            }
        }
    }
}

$page_title = 'Upload Certificate';
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
                    <h2 class="fw-bold mb-0">Upload Certificate</h2>
                    <a href="trainees.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>

                <!-- Trainee Info -->
                <div class="alert alert-info">
                    <strong>Trainee:</strong> <?php echo htmlspecialchars($trainee['first_name'] . ' ' . $trainee['last_name']); ?> 
                    (<?php echo htmlspecialchars($trainee['email']); ?>)
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

                <div class="table-card">
                    <h5 class="fw-bold mb-3">Certificate Information</h5>
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="certificate_name" class="form-label">Certificate Name *</label>
                                <input type="text" class="form-control" id="certificate_name" name="certificate_name" required>
                                <small class="text-muted">e.g., "Web Development Training Certificate"</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="training_record_id" class="form-label">Related Training *</label>
                                <select class="form-select" id="training_record_id" name="training_record_id" required>
                                    <option value="">Select Training...</option>
                                    <?php while ($record = mysqli_fetch_assoc($training_records)): ?>
                                        <option value="<?php echo $record['id']; ?>">
                                            <?php echo htmlspecialchars($record['training_type']); ?> 
                                            (<?php echo format_date($record['start_date']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label for="certificate_file" class="form-label">Certificate File *</label>
                                <input type="file" class="form-control" id="certificate_file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Allowed formats: PDF, JPG, PNG (Max 5MB)</small>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-upload"></i> Upload Certificate
                                </button>
                                <a href="trainees.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
