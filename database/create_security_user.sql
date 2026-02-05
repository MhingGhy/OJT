-- ============================================================================
-- OJT System - Secure Database User Setup
-- ============================================================================
-- This script creates a dedicated MySQL user with minimal privileges
-- for enhanced security in production environments.
--
-- IMPORTANT: Run this script BEFORE deploying to production!
-- ============================================================================

-- ============================================================================
-- STEP 1: Create Database User
-- ============================================================================

-- For LOCALHOST (Development/Single Server):
-- Uncomment this line for local development or single-server deployment
-- CREATE USER IF NOT EXISTS 'ojt_user'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD_NOW!';

-- For PRODUCTION (Remote Database Server):
-- Replace 'your_web_server_ip' with your actual web server IP address
-- Example: CREATE USER IF NOT EXISTS 'ojt_user'@'192.168.1.100' IDENTIFIED BY 'YourStrongPassword123!@#';
CREATE USER IF NOT EXISTS 'ojt_user'@'%' IDENTIFIED BY 'CHANGE_THIS_PASSWORD_NOW!';

-- ============================================================================
-- SECURITY NOTES:
-- ============================================================================
-- 1. ALWAYS change 'CHANGE_THIS_PASSWORD_NOW!' to a strong password
--    - Minimum 16 characters
--    - Mix of uppercase, lowercase, numbers, and special characters
--    - Example: 'Xk9$mP2#vL8@qR5!nW3^'
--
-- 2. For production, NEVER use '%' (allows any host)
--    - Use specific IP: 'ojt_user'@'192.168.1.100'
--    - Or domain: 'ojt_user'@'yourdomain.com'
--    - Or localhost: 'ojt_user'@'localhost'
--
-- 3. After creating the user, update includes/config.php:
--    $db_user = 'ojt_user';
--    $db_pass = 'your_strong_password_here';
-- ============================================================================

-- ============================================================================
-- STEP 2: Grant Minimal Required Privileges
-- ============================================================================

-- Grant only necessary privileges on the ojt_system database
GRANT SELECT, INSERT, UPDATE, DELETE ON ojt_system.* TO 'ojt_user'@'%';

-- ============================================================================
-- PRIVILEGES EXPLANATION:
-- ============================================================================
-- GRANTED:
--   - SELECT: Read data from tables
--   - INSERT: Add new records
--   - UPDATE: Modify existing records
--   - DELETE: Remove records
--
-- NOT GRANTED (for security):
--   - DROP: Prevents accidental/malicious table deletion
--   - CREATE: Prevents unauthorized table creation
--   - ALTER: Prevents schema modifications
--   - GRANT OPTION: Prevents privilege escalation
--   - FILE: Prevents reading/writing server files
--   - SUPER: Prevents administrative operations
-- ============================================================================

-- ============================================================================
-- STEP 3: Apply Changes
-- ============================================================================

FLUSH PRIVILEGES;

-- ============================================================================
-- STEP 4: Verification (Optional - for checking setup)
-- ============================================================================

-- Verify the user was created
SELECT User, Host FROM mysql.user WHERE User = 'ojt_user';

-- Show granted privileges
SHOW GRANTS FOR 'ojt_user'@'%';

-- ============================================================================
-- DEPLOYMENT CHECKLIST:
-- ============================================================================
-- [ ] 1. Change the password to a strong, unique password
-- [ ] 2. Replace '%' with specific host (IP or domain) for production
-- [ ] 3. Run this script on your production MySQL server as root
-- [ ] 4. Update includes/config.php with new credentials
-- [ ] 5. Test database connection with new user
-- [ ] 6. Remove or secure this file after deployment
-- [ ] 7. Never commit passwords to version control
-- ============================================================================

-- ============================================================================
-- EXAMPLE FOR PRODUCTION:
-- ============================================================================
-- CREATE USER IF NOT EXISTS 'ojt_user'@'192.168.1.100' IDENTIFIED BY 'Xk9$mP2#vL8@qR5!nW3^';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON ojt_system.* TO 'ojt_user'@'192.168.1.100';
-- FLUSH PRIVILEGES;
-- ============================================================================
