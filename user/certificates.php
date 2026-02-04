<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

require_login();

// Get trainee information
$trainee_id = $_SESSION['trainee_id'];

// Get all certificates for this trainee
$stmt = mysqli_prepare($conn, "
    SELECT c.*, tr.training_type 
    FROM certificates c
    LEFT JOIN training_records tr ON c.training_record_id = tr.id
    WHERE c.trainee_id = ?
    ORDER BY c.uploaded_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $trainee_id);
mysqli_stmt_execute($stmt);
$certificates = mysqli_stmt_get_result($stmt);

$page_title = 'My Certificates';
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
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person"></i> My Profile
                        </a>
                        <a class="nav-link active" href="certificates.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">My Certificates</h2>
                    <span class="badge bg-primary fs-6"><?php echo mysqli_num_rows($certificates); ?> Total</span>
                </div>

                <?php if (mysqli_num_rows($certificates) > 0): ?>
                    <div class="certificate-grid">
                        <?php while ($cert = mysqli_fetch_assoc($certificates)): ?>
                            <?php
                            $file_ext = strtolower(pathinfo($cert['file_name'], PATHINFO_EXTENSION));
                            $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png']);
                            $is_pdf = $file_ext === 'pdf';
                            ?>
                            <div class="certificate-card">
                                <div class="certificate-preview">
                                    <?php if ($is_image): ?>
                                        <img src="<?php echo htmlspecialchars($cert['file_path']); ?>" 
                                             alt="Certificate" 
                                             class="img-fluid" 
                                             style="width: 100%; height: 200px; object-fit: cover;">
                                    <?php elseif ($is_pdf): ?>
                                        <i class="bi bi-file-pdf"></i>
                                    <?php else: ?>
                                        <i class="bi bi-file-earmark"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="certificate-body">
                                    <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($cert['certificate_name']); ?></h6>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-bookmark"></i> 
                                        <?php echo htmlspecialchars($cert['training_type'] ?? 'General'); ?>
                                    </p>
                                    <?php if ($cert['description']): ?>
                                        <p class="small text-muted mb-2"><?php echo htmlspecialchars($cert['description']); ?></p>
                                    <?php endif; ?>
                                    <p class="small text-muted mb-3">
                                        <i class="bi bi-calendar"></i> 
                                        Uploaded: <?php echo format_date($cert['uploaded_at']); ?>
                                    </p>
                                    <div class="d-grid gap-2">
                                        <?php if ($is_image): ?>
                                            <a href="<?php echo htmlspecialchars($cert['file_path']); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo htmlspecialchars($cert['file_path']); ?>" 
                                           download 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-award" style="font-size: 5rem; color: #e5e7eb;"></i>
                        </div>
                        <h4 class="text-muted">No Certificates Yet</h4>
                        <p class="text-muted">Your certificates will appear here once they are uploaded by the administrator.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
