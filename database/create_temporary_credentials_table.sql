-- Create table to store temporary credentials
CREATE TABLE IF NOT EXISTS temporary_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trainee_id INT NOT NULL,
    temp_username VARCHAR(100) NOT NULL,
    temp_password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainee_id) REFERENCES trainees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment
ALTER TABLE temporary_credentials COMMENT = 'Stores temporary credentials for new trainees until first password change';
