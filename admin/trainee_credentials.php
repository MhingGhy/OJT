<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/security.php';

require_admin();
set_security_headers();

$page_title = 'Trainee Credentials';

// Get all trainees who haven't changed their password yet (FIXED: Using prepared statement)
$query = "SELECT 
    t.id,
    t.first_name,
    t.last_name,
    tc.temp_username,
    tc.temp_password,
    tc.created_at,
    u.require_password_change
FROM trainees t
INNER JOIN users u ON t.id = u.trainee_id
INNER JOIN temporary_credentials tc ON u.id = tc.user_id
WHERE u.require_password_change = 1
ORDER BY tc.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}

// Check if query failed (likely due to missing table)
if ($result === false) {
    $query_error = mysqli_error($conn);
    $table_missing = strpos($query_error, "temporary_credentials") !== false && strpos($query_error, "doesn't exist") !== false;
}
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
                        <a class="nav-link active" href="trainee_credentials.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">
                            <i class="bi bi-key-fill text-primary"></i> Trainee Credentials
                        </h2>
                        <p class="text-muted mb-0">Temporary credentials for trainees pending first login</p>
                    </div>
                    <a href="add_trainee.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Trainee
                    </a>
                </div>

                <?php if (isset($table_missing) && $table_missing): ?>
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Database Table Missing</h5>
                        <p class="mb-3">The <code>temporary_credentials</code> table does not exist in the database. This table is required for the credential backlog feature.</p>
                        <hr>
                        <h6 class="fw-bold mb-2">To fix this issue:</h6>
                        <ol class="mb-3">
                            <li>Open <strong>phpMyAdmin</strong> or your MySQL client</li>
                            <li>Select the <code>ojt_system</code> database</li>
                            <li>Go to the <strong>SQL</strong> tab</li>
                            <li>Run the migration script located at:<br>
                                <code class="d-block mt-2 p-2 bg-dark text-white rounded">database/create_temporary_credentials_table.sql</code>
                            </li>
                        </ol>
                        <a href="../database/create_temporary_credentials_table.sql" class="btn btn-primary" target="_blank">
                            <i class="bi bi-file-earmark-code"></i> View Migration Script
                        </a>
                    </div>
                <?php elseif ($result === false): ?>
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Database Error</h5>
                        <p class="mb-0">An error occurred while fetching credentials: <?php echo htmlspecialchars(mysqli_error($conn)); ?></p>
                    </div>
                <?php elseif (mysqli_num_rows($result) > 0): ?>
                    <div class="table-card">
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="bi bi-info-circle-fill"></i> 
                            <strong>Note:</strong> These credentials are visible until the trainee changes their password on first login. 
                            Please share these credentials securely with the respective trainees.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Trainee Name</th>
                                        <th>Username</th>
                                        <th>Temporary Password</th>
                                        <th>Status</th>
                                        <th>Date Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <code class="bg-light px-2 py-1 rounded">
                                                        <?php echo htmlspecialchars($row['temp_username']); ?>
                                                    </code>
                                                    <button class="btn btn-sm btn-outline-secondary copy-btn" 
                                                            data-copy-text="<?php echo htmlspecialchars($row['temp_username']); ?>"
                                                            title="Copy username">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <code class="bg-warning bg-opacity-10 px-2 py-1 rounded text-dark">
                                                        <?php echo htmlspecialchars($row['temp_password']); ?>
                                                    </code>
                                                    <button class="btn btn-sm btn-outline-secondary copy-btn" 
                                                            data-copy-text="<?php echo htmlspecialchars($row['temp_password']); ?>"
                                                            title="Copy password">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock-history"></i> Pending First Login
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y g:i A', strtotime($row['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <a href="view_trainee.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="View Details">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning mt-4 mb-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> 
                            <strong>Security Notice:</strong> Temporary credentials are stored securely and will be automatically removed from this list once the trainee changes their password on first login.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-card text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                        </div>
                        <h4 class="text-muted mb-3">No Pending Credentials</h4>
                        <p class="text-muted mb-4">
                            All trainees have completed their first login and changed their passwords.
                        </p>
                        <a href="add_trainee.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add New Trainee
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/copy-credentials.js"></script>
</body>
</html>
