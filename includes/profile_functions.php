<?php
/**
 * Profile Helper Functions
 * Functions for managing trainee portfolio data
 */

// Get trainee profile data
function get_trainee_profile($trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM trainees WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $trainee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get trainee skills
function get_trainee_skills($trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM trainee_skills WHERE trainee_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $trainee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get trainee projects
function get_trainee_projects($trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM trainee_projects WHERE trainee_id = ? ORDER BY start_date DESC");
    mysqli_stmt_bind_param($stmt, "i", $trainee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get trainee certifications
function get_trainee_certifications($trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM trainee_certifications WHERE trainee_id = ? ORDER BY issue_date DESC");
    mysqli_stmt_bind_param($stmt, "i", $trainee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get trainee experiences
function get_trainee_experiences($trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM trainee_experiences WHERE trainee_id = ? ORDER BY is_current DESC, start_date DESC");
    mysqli_stmt_bind_param($stmt, "i", $trainee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get trainee media
function get_trainee_media($trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM trainee_media WHERE trainee_id = ? ORDER BY uploaded_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $trainee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Add skill
function add_skill($trainee_id, $skill_name, $proficiency_level, $conn) {
    $stmt = mysqli_prepare($conn, "INSERT INTO trainee_skills (trainee_id, skill_name, proficiency_level) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $trainee_id, $skill_name, $proficiency_level);
    return mysqli_stmt_execute($stmt);
}

// Delete skill
function delete_skill($skill_id, $trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "DELETE FROM trainee_skills WHERE id = ? AND trainee_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $skill_id, $trainee_id);
    return mysqli_stmt_execute($stmt);
}

// Add project
function add_project($trainee_id, $data, $conn) {
    $stmt = mysqli_prepare($conn, "INSERT INTO trainee_projects (trainee_id, project_name, description, technologies_used, project_url, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssss", 
        $trainee_id, 
        $data['project_name'], 
        $data['description'], 
        $data['technologies_used'], 
        $data['project_url'], 
        $data['start_date'], 
        $data['end_date']
    );
    return mysqli_stmt_execute($stmt);
}

// Delete project
function delete_project($project_id, $trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "DELETE FROM trainee_projects WHERE id = ? AND trainee_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $project_id, $trainee_id);
    return mysqli_stmt_execute($stmt);
}

// Add certification
function add_certification($trainee_id, $data, $conn) {
    $stmt = mysqli_prepare($conn, "INSERT INTO trainee_certifications (trainee_id, certification_name, issuing_organization, issue_date, expiry_date, credential_id, credential_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssss", 
        $trainee_id, 
        $data['certification_name'], 
        $data['issuing_organization'], 
        $data['issue_date'], 
        $data['expiry_date'], 
        $data['credential_id'], 
        $data['credential_url']
    );
    return mysqli_stmt_execute($stmt);
}

// Delete certification
function delete_certification($cert_id, $trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "DELETE FROM trainee_certifications WHERE id = ? AND trainee_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $cert_id, $trainee_id);
    return mysqli_stmt_execute($stmt);
}

// Add experience
function add_experience($trainee_id, $data, $conn) {
    $stmt = mysqli_prepare($conn, "INSERT INTO trainee_experiences (trainee_id, position, company, location, start_date, end_date, description, is_current) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssssi", 
        $trainee_id, 
        $data['position'], 
        $data['company'], 
        $data['location'], 
        $data['start_date'], 
        $data['end_date'], 
        $data['description'], 
        $data['is_current']
    );
    return mysqli_stmt_execute($stmt);
}

// Delete experience
function delete_experience($exp_id, $trainee_id, $conn) {
    $stmt = mysqli_prepare($conn, "DELETE FROM trainee_experiences WHERE id = ? AND trainee_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $exp_id, $trainee_id);
    return mysqli_stmt_execute($stmt);
}

// Update trainee profile
function update_trainee_profile($trainee_id, $data, $conn) {
    $stmt = mysqli_prepare($conn, "UPDATE trainees SET first_name = ?, last_name = ?, middle_name = ?, age = ?, sex = ?, email = ?, phone = ?, address = ?, bio = ?, linkedin_url = ?, github_url = ?, portfolio_url = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssississsssi", 
        $data['first_name'], 
        $data['last_name'], 
        $data['middle_name'], 
        $data['age'], 
        $data['sex'], 
        $data['email'], 
        $data['phone'], 
        $data['address'], 
        $data['bio'], 
        $data['linkedin_url'], 
        $data['github_url'], 
        $data['portfolio_url'], 
        $trainee_id
    );
    return mysqli_stmt_execute($stmt);
}

// Get proficiency color
function get_proficiency_color($level) {
    switch($level) {
        case 'Beginner': return 'secondary';
        case 'Intermediate': return 'info';
        case 'Advanced': return 'primary';
        case 'Expert': return 'success';
        default: return 'secondary';
    }
}

// Format date range
function format_date_range($start_date, $end_date, $is_current = false) {
    $start = date('M Y', strtotime($start_date));
    if ($is_current || empty($end_date)) {
        return $start . ' - Present';
    }
    $end = date('M Y', strtotime($end_date));
    return $start . ' - ' . $end;
}

// Calculate profile completion percentage
function calculate_profile_completion($trainee_id, $conn) {
    $profile = get_trainee_profile($trainee_id, $conn);
    $skills = get_trainee_skills($trainee_id, $conn);
    $projects = get_trainee_projects($trainee_id, $conn);
    $certifications = get_trainee_certifications($trainee_id, $conn);
    $experiences = get_trainee_experiences($trainee_id, $conn);
    
    $total = 0;
    $completed = 0;
    
    // Basic info (20%)
    $total += 20;
    if (!empty($profile['bio'])) $completed += 10;
    if (!empty($profile['profile_picture'])) $completed += 10;
    
    // Skills (20%)
    $total += 20;
    if (count($skills) > 0) $completed += 20;
    
    // Projects (20%)
    $total += 20;
    if (count($projects) > 0) $completed += 20;
    
    // Certifications (20%)
    $total += 20;
    if (count($certifications) > 0) $completed += 20;
    
    // Experience (20%)
    $total += 20;
    if (count($experiences) > 0) $completed += 20;
    
    return round(($completed / $total) * 100);
}
?>
