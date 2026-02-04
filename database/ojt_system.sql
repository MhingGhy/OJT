-- OJT Information and Tracking System Database
-- Updated: 2026-01-23
-- Database: ojt_system

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `trainee_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `trainee_id` (`trainee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `trainees`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `trainees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `course` varchar(100) NOT NULL,
  `school` varchar(150) NOT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`last_name`, `first_name`),
  KEY `idx_course` (`course`),
  KEY `idx_school` (`school`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `training_records`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `training_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainee_id` int(11) NOT NULL,
  `training_type` varchar(100) NOT NULL,
  `training_description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Ongoing',
  `completion_percentage` int(11) DEFAULT 0,
  `supervisor_name` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `skills_acquired` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trainee_id` (`trainee_id`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`start_date`, `end_date`),
  CONSTRAINT `fk_training_trainee` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `certificates`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_record_id` int(11) NOT NULL,
  `trainee_id` int(11) NOT NULL,
  `certificate_name` varchar(150) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `training_record_id` (`training_record_id`),
  KEY `trainee_id` (`trainee_id`),
  CONSTRAINT `fk_cert_training` FOREIGN KEY (`training_record_id`) REFERENCES `training_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cert_trainee` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert sample data
-- --------------------------------------------------------

-- Insert sample trainees
INSERT INTO `trainees` (`first_name`, `last_name`, `middle_name`, `age`, `sex`, `email`, `phone`, `address`, `course`, `school`, `year_level`) VALUES
('Juan', 'Dela Cruz', 'Santos', 21, 'Male', 'juan.delacruz@email.com', '09171234567', '123 Main St, Manila', 'Bachelor of Science in Information Technology', 'University of the Philippines', '4th Year'),
('Maria', 'Garcia', 'Reyes', 20, 'Female', 'maria.garcia@email.com', '09187654321', '456 Oak Ave, Quezon City', 'Bachelor of Science in Computer Science', 'Ateneo de Manila University', '3rd Year'),
('Pedro', 'Santos', 'Lopez', 22, 'Male', 'pedro.santos@email.com', '09191234567', '789 Pine Rd, Makati', 'Bachelor of Science in Computer Engineering', 'De La Salle University', '4th Year'),
('Ana', 'Mendoza', 'Torres', 21, 'Female', 'ana.mendoza@email.com', '09198765432', '321 Elm St, Pasig', 'Bachelor of Science in Information Systems', 'University of Santo Tomas', '4th Year');

-- NOTE: Password hashes below are placeholders
-- After importing this database, run: http://localhost/ojt-system/database/update_passwords.php
-- This will set the correct passwords: admin123 for admin, password123 for users

-- Insert admin user (password will be set to: admin123)
INSERT INTO `users` (`username`, `password`, `email`, `role`, `trainee_id`) VALUES
('admin', '$2y$10$placeholder_hash_run_update_script', 'admin@ojt-system.local', 'admin', NULL);

-- Insert sample users for trainees (password will be set to: password123)
INSERT INTO `users` (`username`, `password`, `email`, `role`, `trainee_id`) VALUES
('juan.delacruz', '$2y$10$placeholder_hash_run_update_script', 'juan.delacruz@email.com', 'user', 1),
('maria.garcia', '$2y$10$placeholder_hash_run_update_script', 'maria.garcia@email.com', 'user', 2),
('pedro.santos', '$2y$10$placeholder_hash_run_update_script', 'pedro.santos@email.com', 'user', 3),
('ana.mendoza', '$2y$10$placeholder_hash_run_update_script', 'ana.mendoza@email.com', 'user', 4);

-- Insert sample training records
INSERT INTO `training_records` (`trainee_id`, `training_type`, `training_description`, `start_date`, `end_date`, `status`, `completion_percentage`, `supervisor_name`, `department`, `skills_acquired`) VALUES
(1, 'Web Development Training', 'Full-stack web development using PHP and MySQL', '2025-06-01', '2025-09-30', 'Completed', 100, 'Mr. Roberto Cruz', 'IT Department', 'PHP, MySQL, HTML, CSS, JavaScript'),
(2, 'Database Administration', 'Database design, optimization, and management', '2025-07-01', '2025-10-31', 'Ongoing', 75, 'Ms. Linda Reyes', 'Database Team', 'MySQL, PostgreSQL, Database Design'),
(3, 'Network Administration', 'Network setup, security, and maintenance', '2025-05-15', '2025-08-15', 'Completed', 100, 'Engr. Carlos Mendez', 'Network Operations', 'Cisco, Network Security, TCP/IP'),
(4, 'Mobile App Development', 'Android and iOS application development', '2025-08-01', '2025-11-30', 'Ongoing', 60, 'Mr. David Tan', 'Mobile Development', 'Java, Kotlin, Swift, React Native');

-- Add foreign key constraint for users table
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_trainee` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE SET NULL;
