<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="landing-page">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="display-3 fw-bold mb-4 animate-fade-in">
                            OJT Information & Tracking System
                        </h1>
                        <p class="lead mb-4 animate-fade-in-delay-1">
                            Streamline your On-the-Job Training management with our comprehensive tracking platform. 
                            Monitor progress, manage trainees, and track achievements all in one place.
                        </p>
                        <div class="d-flex gap-3 animate-fade-in-delay-2">
                            <a href="login.php" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image animate-float">
                        <div class="card glass-card p-5">
                            <div class="text-center">
                                <div class="icon-circle mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-clipboard-data" viewBox="0 0 16 16">
                                        <path d="M4 11a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0v-1zm6-4a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0V7zM7 9a1 1 0 0 1 2 0v3a1 1 0 1 1-2 0V9z"/>
                                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                                    </svg>
                                </div>
                                <h3 class="mb-3">Track Your Journey</h3>
                                <p class="text-muted">Monitor training progress, view certificates, and manage your professional development.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">What is OJT?</h2>
                <p class="lead text-muted">On-the-Job Training is a hands-on method of teaching skills, knowledge, and competencies needed for a particular job within the workplace.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card card glass-card h-100 p-4">
                        <div class="feature-icon mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                            </svg>
                        </div>
                        <h4 class="mb-3">Trainee Management</h4>
                        <p class="text-muted">Efficiently manage trainee information, demographics, and training profiles in one centralized system.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card card glass-card h-100 p-4">
                        <div class="feature-icon mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-graph-up" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"/>
                            </svg>
                        </div>
                        <h4 class="mb-3">Progress Tracking</h4>
                        <p class="text-muted">Monitor training progress with detailed analytics, completion rates, and performance metrics.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card card glass-card h-100 p-4">
                        <div class="feature-icon mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
                                <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                                <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                            </svg>
                        </div>
                        <h4 class="mb-3">Certificate Management</h4>
                        <p class="text-muted">Upload, store, and manage training certificates with secure document handling.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section py-5">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h2 class="display-4 fw-bold text-primary">100%</h2>
                        <p class="text-muted">Secure</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h2 class="display-4 fw-bold text-success">24/7</h2>
                        <p class="text-muted">Access</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h2 class="display-4 fw-bold text-info">Real-time</h2>
                        <p class="text-muted">Updates</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h2 class="display-4 fw-bold text-warning">Easy</h2>
                        <p class="text-muted">To Use</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container text-center">
            <p class="mb-0 text-muted">&copy; 2026 OJT Tracking System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
