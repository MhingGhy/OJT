-- Add require_password_change column to users table
-- This flag indicates whether a user must change their password on next login
-- Default is 0 (no change required) for existing users
-- New users created by admin will have this set to 1

ALTER TABLE users 
ADD COLUMN require_password_change TINYINT(1) DEFAULT 0 
COMMENT 'Flag to force password change on next login (0=no, 1=yes)';

-- Update existing users to not require password change
UPDATE users SET require_password_change = 0 WHERE require_password_change IS NULL;
