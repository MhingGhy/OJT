<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

require_login();

// Get trainee information
$trainee_id = $_SESSION['trainee_id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM trainees WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);
mysqli_stmt_execute($stmt);
$trainee = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get training records
$stmt = mysqli_prepare($conn, "SELECT * FROM training_records WHERE trainee_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);
mysqli_stmt_execute($stmt);
$training_records = mysqli_stmt_get_result($stmt);

// Get certificate count
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM certificates WHERE trainee_id = ?");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);
mysqli_stmt_execute($stmt);
$cert_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

// Count completed trainings
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM training_records WHERE trainee_id = ? AND status = 'Completed'");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);
mysqli_stmt_execute($stmt);
$completed_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

$page_title = 'Dashboard';
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
                        <i class="bi bi-clipboard-data text-primary"></i> OJT System
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="index.php">
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

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($trainee['first_name']); ?>! 👋</h2>
                        <p class="text-muted mb-0">Here's your training overview</p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Last login: <?php echo format_datetime($_SESSION['last_activity']); ?></small>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card-dashboard primary">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Trainings</p>
                                    <h3 class="fw-bold mb-0"><?php echo mysqli_num_rows($training_records); mysqli_data_seek($training_records, 0); ?></h3>
                                </div>
                                <div class="stat-icon primary">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card-dashboard success">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Completed</p>
                                    <h3 class="fw-bold mb-0"><?php echo $completed_count; ?></h3>
                                </div>
                                <div class="stat-icon success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card-dashboard warning">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Certificates</p>
                                    <h3 class="fw-bold mb-0"><?php echo $cert_count; ?></h3>
                                </div>
                                <div class="stat-icon warning">
                                    <i class="bi bi-award"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Training Records -->
                <div class="table-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">My Training Records</h5>
                        <a href="profile.php" class="btn btn-sm btn-outline-primary">View Full Profile</a>
                    </div>
                    
                    <?php if (mysqli_num_rows($training_records) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Training Type</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>Supervisor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = mysqli_fetch_assoc($training_records)): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($record['training_type']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($record['department'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td>
                                                <?php echo format_date($record['start_date']); ?>
                                                <br>
                                                <small class="text-muted">to <?php echo format_date($record['end_date']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo get_status_badge($record['status']); ?>">
                                                    <?php echo $record['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?php echo get_status_badge($record['status']); ?>" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $record['completion_percentage']; ?>%"
                                                         aria-valuenow="<?php echo $record['completion_percentage']; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?php echo $record['completion_percentage']; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['supervisor_name'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No training records found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
