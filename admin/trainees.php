<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/security.php';

require_admin();
set_security_headers();

// Get all trainees with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Count total trainees (FIXED: Using prepared statement)
$count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM trainees");
mysqli_stmt_execute($count_stmt);
$total_result = mysqli_stmt_get_result($count_stmt);
$total_row = mysqli_fetch_assoc($total_result);
$total_trainees = $total_row['total'];
$total_pages = ceil($total_trainees / $per_page);

// Get trainees for current page
$stmt = mysqli_prepare($conn, "SELECT * FROM trainees ORDER BY created_at DESC LIMIT ? OFFSET ?");
mysqli_stmt_bind_param($stmt, "ii", $per_page, $offset);
mysqli_stmt_execute($stmt);
$trainees = mysqli_stmt_get_result($stmt);

$page_title = 'Manage Trainees';
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
                    <h2 class="fw-bold mb-0">Manage Trainees</h2>
                    <a href="add_trainee.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Trainee
                    </a>
                </div>

                <!-- Search Box -->
                <div class="table-card mb-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email, course, or school...">
                    </div>
                </div>

                <!-- Trainees Table -->
                <div class="table-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">All Trainees (<?php echo $total_trainees; ?>)</h5>
                    </div>
                    
                    <?php if (mysqli_num_rows($trainees) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Course</th>
                                        <th>School</th>
                                        <th>Sex</th>
                                        <th>Age</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($trainee = mysqli_fetch_assoc($trainees)): ?>
                                        <tr>
                                            <td><?php echo $trainee['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($trainee['first_name'] . ' ' . $trainee['last_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($trainee['email']); ?></td>
                                            <td><?php echo htmlspecialchars($trainee['course']); ?></td>
                                            <td><?php echo htmlspecialchars($trainee['school']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $trainee['sex'] === 'Male' ? 'primary' : 'danger'; ?>">
                                                    <?php echo $trainee['sex']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $trainee['age']; ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view_trainee.php?id=<?php echo $trainee['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info action-btn"
                                                       title="View Profile">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit_trainee.php?id=<?php echo $trainee['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary action-btn"
                                                       title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="upload_certificate.php?trainee_id=<?php echo $trainee['id']; ?>" 
                                                       class="btn btn-sm btn-outline-success action-btn"
                                                       title="Upload Certificate">
                                                        <i class="bi bi-upload"></i>
                                                    </a>
                                                    <a href="delete_trainee.php?id=<?php echo $trainee['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger action-btn delete-btn"
                                                       title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No trainees found. <a href="add_trainee.php">Add your first trainee</a>
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
