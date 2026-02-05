-- Create dedicated database user for OJT System
-- This user has limited privileges for better security
-- Run this script as root user

-- Create the user (change the password to something strong)
CREATE USER IF NOT EXISTS 'ojt_user'@'localhost' IDENTIFIED BY 'OJT_SecurePass2024!';

-- Grant only necessary privileges on the ojt_system database
GRANT SELECT, INSERT, UPDATE, DELETE ON ojt_system.* TO 'ojt_user'@'localhost';

-- Do NOT grant these dangerous privileges:
-- - DROP (prevents accidental table deletion)
-- - CREATE (prevents unauthorized table creation)
-- - ALTER (prevents schema modifications)
-- - GRANT OPTION (prevents privilege escalation)

-- Apply the changes
FLUSH PRIVILEGES;

-- Verify the user was created
SELECT User, Host FROM mysql.user WHERE User = 'ojt_user';

-- Show granted privileges
SHOW GRANTS FOR 'ojt_user'@'localhost';
