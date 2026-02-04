<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

require_admin();

// Get statistics
$total_trainees = count_total_trainees();
$male_count = count_trainees_by_gender('Male');
$female_count = count_trainees_by_gender('Female');
$completed_count = count_training_by_status('Completed');
$ongoing_count = count_training_by_status('Ongoing');

// Get recent trainees
$recent_trainees = get_recent_trainees(5);

$page_title = 'Admin Dashboard';
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
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="trainees.php">
                            <i class="bi bi-people"></i> Manage Trainees
                        </a>
                        <a class="nav-link" href="trainee_credentials.php">
                            <i class="bi bi-key-fill"></i> Trainee Credentials
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
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Admin Dashboard</h2>
                        <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                    </div>
                    <a href="add_trainee.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Trainee
                    </a>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card-dashboard primary">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Trainees</p>
                                    <h3 class="fw-bold mb-0"><?php echo $total_trainees; ?></h3>
                                </div>
                                <div class="stat-icon primary">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card-dashboard info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Male Trainees</p>
                                    <h3 class="fw-bold mb-0"><?php echo $male_count; ?></h3>
                                </div>
                                <div class="stat-icon info">
                                    <i class="bi bi-gender-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card-dashboard warning">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Female Trainees</p>
                                    <h3 class="fw-bold mb-0"><?php echo $female_count; ?></h3>
                                </div>
                                <div class="stat-icon warning">
                                    <i class="bi bi-gender-female"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
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
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="table-card">
                            <h5 class="fw-bold mb-3">Gender Distribution</h5>
                            <div class="chart-container">
                                <canvas id="genderChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="table-card">
                            <h5 class="fw-bold mb-3">Training Status</h5>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Trainees -->
                <div class="table-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Recent Trainees</h5>
                        <a href="trainees.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <?php if (mysqli_num_rows($recent_trainees) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Course</th>
                                        <th>School</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($trainee = mysqli_fetch_assoc($recent_trainees)): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($trainee['first_name'] . ' ' . $trainee['last_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $trainee['sex']; ?>, <?php echo $trainee['age']; ?> years</small>
                                            </td>
                                            <td><?php echo htmlspecialchars($trainee['email']); ?></td>
                                            <td><?php echo htmlspecialchars($trainee['course']); ?></td>
                                            <td><?php echo htmlspecialchars($trainee['school']); ?></td>
                                            <td><?php echo format_date($trainee['created_at']); ?></td>
                                            <td>
                                                <a href="edit_trainee.php?id=<?php echo $trainee['id']; ?>" class="btn btn-sm btn-outline-primary action-btn">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="upload_certificate.php?trainee_id=<?php echo $trainee['id']; ?>" class="btn btn-sm btn-outline-success action-btn">
                                                    <i class="bi bi-upload"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No trainees found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [<?php echo $male_count; ?>, <?php echo $female_count; ?>],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: [
                        'rgba(99, 102, 241, 1)',
                        'rgba(245, 158, 11, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Training Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: ['Completed', 'Ongoing'],
                datasets: [{
                    label: 'Training Records',
                    data: [<?php echo $completed_count; ?>, <?php echo $ongoing_count; ?>],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgba(16, 185, 129, 1)',
                        'rgba(59, 130, 246, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
