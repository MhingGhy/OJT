<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/profile_functions.php';

require_login();

// Get trainee information
$trainee_id = $_SESSION['trainee_id'];
$profile = get_trainee_profile($trainee_id, $conn);
$skills = get_trainee_skills($trainee_id, $conn);
$projects = get_trainee_projects($trainee_id, $conn);
$certifications = get_trainee_certifications($trainee_id, $conn);
$experiences = get_trainee_experiences($trainee_id, $conn);
$media = get_trainee_media($trainee_id, $conn);
$completion = calculate_profile_completion($trainee_id, $conn);

$page_title = 'My Profile';
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
    <style>
        .profile-header {
            background: linear-gradient(
                135deg,
                var(--primary-navy),
                var(--secondary-steel)
            );
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transform: translateX(75px);
        }
        .profile-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .skill-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .project-card, .cert-card, .exp-card {
            border-left: 4px solid var(--steel-blue);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .social-links a {
            display: inline-block;
            margin-right: 1rem;
            color: white;
            font-size: 1.5rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
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
                        <a class="nav-link active" href="profile.php">
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
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <?php if (!empty($profile['profile_picture'])): ?>
                                    <img src="../<?php echo htmlspecialchars($profile['profile_picture']); ?>" alt="Profile" class="profile-picture">
                                <?php else: ?>
                                    <div class="profile-picture d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.2);">
                                        <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <h2 class="fw-bold mb-2">
                                    <?php echo htmlspecialchars(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')); ?>
                                </h2>
                                <p class="mb-2">
                                    <i class="bi bi-mortarboard"></i> <?php echo htmlspecialchars($profile['course'] ?? 'Not specified'); ?>
                                </p>
                                <p class="mb-3">
                                    <i class="bi bi-building"></i> <?php echo htmlspecialchars($profile['school'] ?? 'Not specified'); ?>
                                </p>
                                <div class="social-links">
                                    <?php if (!empty($profile['linkedin_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($profile['linkedin_url']); ?>" target="_blank" title="LinkedIn">
                                            <i class="bi bi-linkedin"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($profile['github_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($profile['github_url']); ?>" target="_blank" title="GitHub">
                                            <i class="bi bi-github"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($profile['portfolio_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($profile['portfolio_url']); ?>" target="_blank" title="Portfolio">
                                            <i class="bi bi-globe"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>Profile Completion</small>
                                        <div class="progress" style="width: 300px; height: 25px; margin-left: 15px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completion; ?>%" aria-valuenow="<?php echo $completion; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $completion; ?>%
                                            </div>
                                        </div>
                                    </div>
                                    <a href="edit_profile.php" class="btn btn-light">
                                        <i class="bi bi-pencil"></i> Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About Section -->
                <div class="profile-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0"><i class="bi bi-person-lines-fill text-primary"></i> About</h4>
                        <a href="edit_profile.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </div>
                    <?php if (!empty($profile['bio'])): ?>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-file-text"></i>
                            <p>Add a bio to tell others about yourself</p>
                            <a href="edit_profile.php" class="btn btn-primary">Add Bio</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><i class="bi bi-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($profile['email'] ?? 'Not provided'); ?></p>
                            <p><i class="bi bi-telephone"></i> <strong>Phone:</strong> <?php echo htmlspecialchars($profile['phone'] ?? 'Not provided'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="bi bi-geo-alt"></i> <strong>Address:</strong> <?php echo htmlspecialchars($profile['address'] ?? 'Not provided'); ?></p>
                            <p><i class="bi bi-calendar"></i> <strong>Age:</strong> <?php echo htmlspecialchars($profile['age'] ?? 'Not specified'); ?> years old</p>
                        </div>
                    </div>
                </div>

                <!-- Skills Section -->
                <div class="profile-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0"><i class="bi bi-gear text-primary"></i> Skills</h4>
                        <a href="edit_profile.php#skills" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Add Skill
                        </a>
                    </div>
                    <?php if (count($skills) > 0): ?>
                        <div>
                            <?php foreach ($skills as $skill): ?>
                                <span class="skill-badge bg-<?php echo get_proficiency_color($skill['proficiency_level']); ?> text-white">
                                    <?php echo htmlspecialchars($skill['skill_name']); ?>
                                    <small>(<?php echo $skill['proficiency_level']; ?>)</small>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-gear"></i>
                            <p>Showcase your skills and expertise</p>
                            <a href="edit_profile.php#skills" class="btn btn-primary">Add Skills</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Projects Section -->
                <div class="profile-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0"><i class="bi bi-folder text-primary"></i> Projects</h4>
                        <a href="edit_profile.php#projects" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Add Project
                        </a>
                    </div>
                    <?php if (count($projects) > 0): ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="project-card">
                                <h5 class="fw-bold"><?php echo htmlspecialchars($project['project_name']); ?></h5>
                                <p class="text-muted mb-2">
                                    <small>
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo format_date_range($project['start_date'], $project['end_date']); ?>
                                    </small>
                                </p>
                                <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                                <?php if (!empty($project['technologies_used'])): ?>
                                    <p class="mb-2">
                                        <strong>Technologies:</strong> 
                                        <span class="text-muted"><?php echo htmlspecialchars($project['technologies_used']); ?></span>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($project['project_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-link-45deg"></i> View Project
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-folder"></i>
                            <p>Add projects to showcase your work</p>
                            <a href="edit_profile.php#projects" class="btn btn-primary">Add Project</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Certifications Section -->
                <div class="profile-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0"><i class="bi bi-award text-primary"></i> Certifications</h4>
                        <a href="edit_profile.php#certifications" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Add Certification
                        </a>
                    </div>
                    <?php if (count($certifications) > 0): ?>
                        <?php foreach ($certifications as $cert): ?>
                            <div class="cert-card">
                                <h5 class="fw-bold"><?php echo htmlspecialchars($cert['certification_name']); ?></h5>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($cert['issuing_organization']); ?></p>
                                <p class="mb-2">
                                    <small>
                                        <i class="bi bi-calendar-check"></i> Issued: <?php echo format_date($cert['issue_date']); ?>
                                        <?php if (!empty($cert['expiry_date'])): ?>
                                            | Expires: <?php echo format_date($cert['expiry_date']); ?>
                                        <?php endif; ?>
                                    </small>
                                </p>
                                <?php if (!empty($cert['credential_id'])): ?>
                                    <p class="mb-2"><strong>Credential ID:</strong> <?php echo htmlspecialchars($cert['credential_id']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($cert['credential_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($cert['credential_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-link-45deg"></i> Verify Credential
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-award"></i>
                            <p>Add your certifications and achievements</p>
                            <a href="edit_profile.php#certifications" class="btn btn-primary">Add Certification</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Experience Section -->
                <div class="profile-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0"><i class="bi bi-briefcase text-primary"></i> Experience</h4>
                        <a href="edit_profile.php#experience" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Add Experience
                        </a>
                    </div>
                    <?php if (count($experiences) > 0): ?>
                        <?php foreach ($experiences as $exp): ?>
                            <div class="exp-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="fw-bold"><?php echo htmlspecialchars($exp['position']); ?></h5>
                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($exp['company']); ?></p>
                                    </div>
                                    <?php if ($exp['is_current']): ?>
                                        <span class="badge bg-success">Current</span>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-2">
                                    <small>
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo format_date_range($exp['start_date'], $exp['end_date'], $exp['is_current']); ?>
                                    </small>
                                    <?php if (!empty($exp['location'])): ?>
                                        | <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($exp['location']); ?>
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($exp['description'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-briefcase"></i>
                            <p>Add your work experience</p>
                            <a href="edit_profile.php#experience" class="btn btn-primary">Add Experience</a>
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
