<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

require_admin();

$results = [];
$search_performed = false;

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['search']) || isset($_GET['status']) || isset($_GET['training_type']))) {
    $search_performed = true;
    
    $search_name = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
    $status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
    $training_type_filter = isset($_GET['training_type']) ? sanitize_input($_GET['training_type']) : '';
    $date_from = isset($_GET['date_from']) ? sanitize_input($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_input($_GET['date_to']) : '';
    
    // Build query
    $query = "SELECT DISTINCT t.*, tr.training_type, tr.status as training_status, tr.start_date, tr.end_date, tr.completion_percentage 
              FROM trainees t 
              LEFT JOIN training_records tr ON t.id = tr.trainee_id 
              WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Add search conditions
    if (!empty($search_name)) {
        $query .= " AND (t.first_name LIKE ? OR t.last_name LIKE ? OR t.email LIKE ? OR t.course LIKE ? OR t.school LIKE ?)";
        $search_param = "%$search_name%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
        $types .= 'sssss';
    }
    
    if (!empty($status_filter)) {
        $query .= " AND tr.status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }
    
    if (!empty($training_type_filter)) {
        $query .= " AND tr.training_type LIKE ?";
        $params[] = "%$training_type_filter%";
        $types .= 's';
    }
    
    if (!empty($date_from)) {
        $query .= " AND tr.end_date >= ?";
        $params[] = $date_from;
        $types .= 's';
    }
    
    if (!empty($date_to)) {
        $query .= " AND tr.end_date <= ?";
        $params[] = $date_to;
        $types .= 's';
    }
    
    $query .= " ORDER BY t.last_name, t.first_name";
    
    // Execute query
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $results = mysqli_stmt_get_result($stmt);
    } else {
        $results = mysqli_query($conn, $query);
    }
}

// Get unique training types for filter
$training_types_query = mysqli_query($conn, "SELECT DISTINCT training_type FROM training_records ORDER BY training_type");

$page_title = 'Search Trainees';
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
                        <a class="nav-link" href="trainees.php">
                            <i class="bi bi-people"></i> Manage Trainees
                        </a>
                        <a class="nav-link active" href="search.php">
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
                <h2 class="fw-bold mb-4">Search Qualified Trainees</h2>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Search for qualified candidates:</strong> Use the filters below to find trainees with specific training, completion status, or date ranges. Perfect for screening candidates for job placements.
                </div>

                <!-- Search Form -->
                <div class="search-form">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search by Name, Email, Course, or School</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                       placeholder="Enter search term...">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="training_type" class="form-label">Training Type</label>
                                <select class="form-select" id="training_type" name="training_type">
                                    <option value="">All Training Types</option>
                                    <?php while ($type = mysqli_fetch_assoc($training_types_query)): ?>
                                        <option value="<?php echo htmlspecialchars($type['training_type']); ?>"
                                                <?php echo (isset($_GET['training_type']) && $_GET['training_type'] === $type['training_type']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['training_type']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="status" class="form-label">Training Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="Completed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Ongoing" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="Cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="date_from" class="form-label">Completed From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from"
                                       value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="date_to" class="form-label">Completed To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to"
                                       value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                <a href="search.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Search Results -->
                <?php if ($search_performed): ?>
                    <div class="table-card mt-4">
                        <h5 class="fw-bold mb-3">
                            Search Results 
                            <?php if ($results): ?>
                                (<?php echo mysqli_num_rows($results); ?> found)
                            <?php endif; ?>
                        </h5>
                        
                        <?php if ($results && mysqli_num_rows($results) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Training</th>
                                            <th>Status</th>
                                            <th>Duration</th>
                                            <th>Progress</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($results)): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo $row['sex']; ?>, <?php echo $row['age']; ?> years</small>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($row['course']); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['school']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['training_type'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if ($row['training_status']): ?>
                                                        <span class="badge bg-<?php echo get_status_badge($row['training_status']); ?>">
                                                            <?php echo $row['training_status']; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['start_date'] && $row['end_date']): ?>
                                                        <?php echo format_date($row['start_date']); ?>
                                                        <br>
                                                        <small class="text-muted">to <?php echo format_date($row['end_date']); ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['completion_percentage'] !== null): ?>
                                                        <div class="progress" style="height: 20px; min-width: 80px;">
                                                            <div class="progress-bar bg-<?php echo get_status_badge($row['training_status']); ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo $row['completion_percentage']; ?>%">
                                                                <?php echo $row['completion_percentage']; ?>%
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="edit_trainee.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary action-btn">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> No trainees found matching your search criteria.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
