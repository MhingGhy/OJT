-- Portfolio System Migration
-- Adds portfolio functionality to trainee profiles
-- Run this after importing ojt_system.sql

-- Add portfolio fields to trainees table
ALTER TABLE `trainees`
ADD COLUMN `bio` TEXT DEFAULT NULL AFTER `address`,
ADD COLUMN `profile_picture` VARCHAR(255) DEFAULT NULL AFTER `bio`,
ADD COLUMN `linkedin_url` VARCHAR(255) DEFAULT NULL AFTER `profile_picture`,
ADD COLUMN `github_url` VARCHAR(255) DEFAULT NULL AFTER `linkedin_url`,
ADD COLUMN `portfolio_url` VARCHAR(255) DEFAULT NULL AFTER `github_url`;

-- Create trainee_skills table
CREATE TABLE IF NOT EXISTS `trainee_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainee_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `proficiency_level` enum('Beginner','Intermediate','Advanced','Expert') NOT NULL DEFAULT 'Beginner',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trainee_id` (`trainee_id`),
  CONSTRAINT `fk_skills_trainee` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create trainee_projects table
CREATE TABLE IF NOT EXISTS `trainee_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainee_id` int(11) NOT NULL,
  `project_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `technologies_used` text DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trainee_id` (`trainee_id`),
  CONSTRAINT `fk_projects_trainee` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create trainee_certifications table
CREATE TABLE IF NOT EXISTS `trainee_certifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainee_id` int(11) NOT NULL,
  `certification_name` varchar(150) NOT NULL,
  `issuing_organization` varchar(150) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `credential_id` varchar(100) DEFAULT NULL,
  `credential_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trainee_id` (`trainee_id`),
  CONSTRAINT `fk_certifications_trainee` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create trainee_experiences table
CREATE TABLE IF NOT EXISTS `trainee_experiences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainee_id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `company` varchar(150) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trainee_id` (`trainee_id`),
  CONSTRAINT `fk_experiences_trainee` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create trainee_media table
CREATE TABLE IF NOT EXISTS `trainee_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainee_id` int(11) NOT NULL,
  `media_type` enum('image','document','video') NOT NULL DEFAULT 'image',
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `caption` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trainee_id` (`trainee_id`),
  CONSTRAINT `fk_media_trainee` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample portfolio data for Juan Dela Cruz (trainee_id = 1)
INSERT INTO `trainee_skills` (`trainee_id`, `skill_name`, `proficiency_level`) VALUES
(1, 'PHP', 'Advanced'),
(1, 'MySQL', 'Advanced'),
(1, 'JavaScript', 'Intermediate'),
(1, 'HTML/CSS', 'Expert'),
(1, 'Git', 'Intermediate');

INSERT INTO `trainee_projects` (`trainee_id`, `project_name`, `description`, `technologies_used`, `project_url`, `start_date`, `end_date`) VALUES
(1, 'E-Commerce Website', 'Developed a full-featured e-commerce platform with shopping cart, payment integration, and admin panel.', 'PHP, MySQL, Bootstrap, JavaScript', 'https://github.com/juan/ecommerce', '2025-01-15', '2025-04-30'),
(1, 'Task Management System', 'Built a collaborative task management application with real-time updates and team collaboration features.', 'PHP, MySQL, AJAX, jQuery', NULL, '2025-05-01', NULL);

INSERT INTO `trainee_certifications` (`trainee_id`, `certification_name`, `issuing_organization`, `issue_date`, `credential_id`, `credential_url`) VALUES
(1, 'PHP Developer Certification', 'Zend Technologies', '2024-11-15', 'ZCE-2024-12345', 'https://www.zend.com/verify/ZCE-2024-12345'),
(1, 'MySQL Database Administrator', 'Oracle', '2024-09-20', 'MYSQL-DBA-2024-67890', 'https://education.oracle.com/verify/MYSQL-DBA-2024-67890');

INSERT INTO `trainee_experiences` (`trainee_id`, `position`, `company`, `location`, `start_date`, `end_date`, `description`, `is_current`) VALUES
(1, 'Junior Web Developer', 'Tech Solutions Inc.', 'Manila, Philippines', '2024-06-01', '2024-12-31', 'Developed and maintained company websites using PHP and MySQL. Collaborated with design team to implement responsive layouts.', 0),
(1, 'Web Development Intern', 'Digital Agency Co.', 'Quezon City, Philippines', '2025-01-15', NULL, 'Currently working on client projects, learning modern web development practices and frameworks.', 1);

-- Update Juan\'s bio
UPDATE `trainees` 
SET `bio` = 'Passionate web developer with expertise in PHP and MySQL. Experienced in building full-stack web applications and committed to writing clean, maintainable code. Always eager to learn new technologies and best practices.',
    `linkedin_url` = 'https://linkedin.com/in/juandelacruz',
    `github_url` = 'https://github.com/juandelacruz'
WHERE `id` = 1;
