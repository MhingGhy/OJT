<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/profile_functions.php';

require_login();

$trainee_id = $_SESSION['trainee_id'];
$profile = get_trainee_profile($trainee_id, $conn);
$skills = get_trainee_skills($trainee_id, $conn);
$projects = get_trainee_projects($trainee_id, $conn);
$certifications = get_trainee_certifications($trainee_id, $conn);
$experiences = get_trainee_experiences($trainee_id, $conn);

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $data = [
                'first_name' => sanitize_input($_POST['first_name']),
                'last_name' => sanitize_input($_POST['last_name']),
                'middle_name' => sanitize_input($_POST['middle_name']),
                'age' => intval($_POST['age']),
                'sex' => sanitize_input($_POST['sex']),
                'email' => sanitize_input($_POST['email']),
                'phone' => sanitize_input($_POST['phone']),
                'address' => sanitize_input($_POST['address']),
                'bio' => sanitize_input($_POST['bio']),
                'linkedin_url' => sanitize_input($_POST['linkedin_url']),
                'github_url' => sanitize_input($_POST['github_url']),
                'portfolio_url' => sanitize_input($_POST['portfolio_url'])
            ];
            
            if (update_trainee_profile($trainee_id, $data, $conn)) {
                $success = 'Profile updated successfully!';
                $profile = get_trainee_profile($trainee_id, $conn);
            } else {
                $error = 'Failed to update profile.';
            }
            break;
            
        case 'add_skill':
            if (add_skill($trainee_id, $_POST['skill_name'], $_POST['proficiency_level'], $conn)) {
                $success = 'Skill added successfully!';
                $skills = get_trainee_skills($trainee_id, $conn);
            } else {
                $error = 'Failed to add skill.';
            }
            break;
            
        case 'delete_skill':
            if (delete_skill($_POST['skill_id'], $trainee_id, $conn)) {
                $success = 'Skill deleted successfully!';
                $skills = get_trainee_skills($trainee_id, $conn);
            }
            break;
            
        case 'add_project':
            $data = [
                'project_name' => sanitize_input($_POST['project_name']),
                'description' => sanitize_input($_POST['description']),
                'technologies_used' => sanitize_input($_POST['technologies_used']),
                'project_url' => sanitize_input($_POST['project_url']),
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'] ?? null
            ];
            if (add_project($trainee_id, $data, $conn)) {
                $success = 'Project added successfully!';
                $projects = get_trainee_projects($trainee_id, $conn);
            } else {
                $error = 'Failed to add project.';
            }
            break;
            
        case 'delete_project':
            if (delete_project($_POST['project_id'], $trainee_id, $conn)) {
                $success = 'Project deleted successfully!';
                $projects = get_trainee_projects($trainee_id, $conn);
            }
            break;
            
        case 'add_certification':
            $data = [
                'certification_name' => sanitize_input($_POST['certification_name']),
                'issuing_organization' => sanitize_input($_POST['issuing_organization']),
                'issue_date' => $_POST['issue_date'],
                'expiry_date' => $_POST['expiry_date'] ?? null,
                'credential_id' => sanitize_input($_POST['credential_id']),
                'credential_url' => sanitize_input($_POST['credential_url'])
            ];
            if (add_certification($trainee_id, $data, $conn)) {
                $success = 'Certification added successfully!';
                $certifications = get_trainee_certifications($trainee_id, $conn);
            } else {
                $error = 'Failed to add certification.';
            }
            break;
            
        case 'delete_certification':
            if (delete_certification($_POST['cert_id'], $trainee_id, $conn)) {
                $success = 'Certification deleted successfully!';
                $certifications = get_trainee_certifications($trainee_id, $conn);
            }
            break;
            
        case 'add_experience':
            $data = [
                'position' => sanitize_input($_POST['position']),
                'company' => sanitize_input($_POST['company']),
                'location' => sanitize_input($_POST['location']),
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'] ?? null,
                'description' => sanitize_input($_POST['description']),
                'is_current' => isset($_POST['is_current']) ? 1 : 0
            ];
            if (add_experience($trainee_id, $data, $conn)) {
                $success = 'Experience added successfully!';
                $experiences = get_trainee_experiences($trainee_id, $conn);
            } else {
                $error = 'Failed to add experience.';
            }
            break;
            
        case 'delete_experience':
            if (delete_experience($_POST['exp_id'], $trainee_id, $conn)) {
                $success = 'Experience deleted successfully!';
                $experiences = get_trainee_experiences($trainee_id, $conn);
            }
            break;
    }
}

$page_title = 'Edit Profile';
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Edit Profile</h2>
                    <a href="profile.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Profile
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Basic Information -->
                <div class="table-card mb-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-person-lines-fill text-primary"></i> Basic Information</h4>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($profile['first_name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($profile['last_name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" value="<?php echo htmlspecialchars($profile['middle_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Age *</label>
                                <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($profile['age']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sex *</label>
                                <select class="form-control" name="sex" required>
                                    <option value="Male" <?php echo $profile['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $profile['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bio</label>
                                <textarea class="form-control" name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control" name="linkedin_url" value="<?php echo htmlspecialchars($profile['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/yourprofile">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">GitHub URL</label>
                                <input type="url" class="form-control" name="github_url" value="<?php echo htmlspecialchars($profile['github_url'] ?? ''); ?>" placeholder="https://github.com/yourusername">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Portfolio URL</label>
                                <input type="url" class="form-control" name="portfolio_url" value="<?php echo htmlspecialchars($profile['portfolio_url'] ?? ''); ?>" placeholder="https://yourportfolio.com">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Change Section -->
                <div class="table-card mb-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-shield-lock text-primary"></i> Security</h4>
                    <p class="text-muted">Manage your account security settings</p>
                    <a href="change_password.php" class="btn btn-outline-primary">
                        <i class="bi bi-key"></i> Change Password
                    </a>
                </div>

                <!-- Skills Section -->
                <div class="table-card mb-4" id="skills">
                    <h4 class="fw-bold mb-3"><i class="bi bi-gear text-primary"></i> Skills</h4>
                    
                    <!-- Add Skill Form -->
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="action" value="add_skill">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="skill_name" placeholder="Skill name (e.g., PHP, JavaScript)" required>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" name="proficiency_level" required>
                                    <option value="">Select proficiency...</option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Skills List -->
                    <?php if (count($skills) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Skill</th>
                                        <th>Proficiency</th>
                                        <th width="100">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($skills as $skill): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($skill['skill_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo get_proficiency_color($skill['proficiency_level']); ?>">
                                                    <?php echo $skill['proficiency_level']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_skill">
                                                    <input type="hidden" name="skill_id" value="<?php echo $skill['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this skill?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No skills added yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Projects Section -->
                <div class="table-card mb-4" id="projects">
                    <h4 class="fw-bold mb-3"><i class="bi bi-folder text-primary"></i> Projects</h4>
                    
                    <!-- Add Project Form -->
                    <form method="POST" class="border p-3 rounded mb-3">
                        <input type="hidden" name="action" value="add_project">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Project Name *</label>
                                <input type="text" class="form-control" name="project_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Project URL</label>
                                <input type="url" class="form-control" name="project_url" placeholder="https://...">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Technologies Used</label>
                                <input type="text" class="form-control" name="technologies_used" placeholder="e.g., PHP, MySQL, JavaScript">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Add Project
                            </button>
                        </div>
                    </form>

                    <!-- Projects List -->
                    <?php if (count($projects) > 0): ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5><?php echo htmlspecialchars($project['project_name']); ?></h5>
                                            <p class="text-muted mb-2">
                                                <?php echo format_date_range($project['start_date'], $project['end_date']); ?>
                                            </p>
                                            <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                                            <?php if ($project['technologies_used']): ?>
                                                <p><strong>Technologies:</strong> <?php echo htmlspecialchars($project['technologies_used']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($project['project_url']): ?>
                                                <a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-link"></i> View Project
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="delete_project">
                                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this project?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No projects added yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Certifications Section -->
                <div class="table-card mb-4" id="certifications">
                    <h4 class="fw-bold mb-3"><i class="bi bi-award text-primary"></i> Certifications</h4>
                    
                    <!-- Add Certification Form -->
                    <form method="POST" class="border p-3 rounded mb-3">
                        <input type="hidden" name="action" value="add_certification">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Certification Name *</label>
                                <input type="text" class="form-control" name="certification_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Issuing Organization *</label>
                                <input type="text" class="form-control" name="issuing_organization" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Issue Date *</label>
                                <input type="date" class="form-control" name="issue_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" name="expiry_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Credential ID</label>
                                <input type="text" class="form-control" name="credential_id">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Credential URL</label>
                                <input type="url" class="form-control" name="credential_url" placeholder="https://...">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Add Certification
                            </button>
                        </div>
                    </form>

                    <!-- Certifications List -->
                    <?php if (count($certifications) > 0): ?>
                        <?php foreach ($certifications as $cert): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5><?php echo htmlspecialchars($cert['certification_name']); ?></h5>
                                            <p class="text-muted"><?php echo htmlspecialchars($cert['issuing_organization']); ?></p>
                                            <p><small>Issued: <?php echo format_date($cert['issue_date']); ?>
                                            <?php if ($cert['expiry_date']): ?>
                                                | Expires: <?php echo format_date($cert['expiry_date']); ?>
                                            <?php endif; ?>
                                            </small></p>
                                            <?php if ($cert['credential_id']): ?>
                                                <p><strong>ID:</strong> <?php echo htmlspecialchars($cert['credential_id']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($cert['credential_url']): ?>
                                                <a href="<?php echo htmlspecialchars($cert['credential_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-link"></i> Verify
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="delete_certification">
                                                <input type="hidden" name="cert_id" value="<?php echo $cert['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this certification?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No certifications added yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Experience Section -->
                <div class="table-card mb-4" id="experience">
                    <h4 class="fw-bold mb-3"><i class="bi bi-briefcase text-primary"></i> Experience</h4>
                    
                    <!-- Add Experience Form -->
                    <form method="POST" class="border p-3 rounded mb-3">
                        <input type="hidden" name="action" value="add_experience">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Position *</label>
                                <input type="text" class="form-control" name="position" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company *</label>
                                <input type="text" class="form-control" name="company" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_current" id="is_current">
                                    <label class="form-check-label" for="is_current">
                                        I currently work here
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Add Experience
                            </button>
                        </div>
                    </form>

                    <!-- Experience List -->
                    <?php if (count($experiences) > 0): ?>
                        <?php foreach ($experiences as $exp): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5><?php echo htmlspecialchars($exp['position']); ?></h5>
                                            <p class="text-muted"><?php echo htmlspecialchars($exp['company']); ?></p>
                                            <p><small>
                                                <?php echo format_date_range($exp['start_date'], $exp['end_date'], $exp['is_current']); ?>
                                                <?php if ($exp['location']): ?>
                                                    | <?php echo htmlspecialchars($exp['location']); ?>
                                                <?php endif; ?>
                                            </small></p>
                                            <?php if ($exp['description']): ?>
                                                <p><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="delete_experience">
                                                <input type="hidden" name="exp_id" value="<?php echo $exp['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this experience?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No experience added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
