# OJT System - Security Setup Instructions

## Database Migration Required

Before using the system, you need to run the database migration to create the `login_attempts` table for brute force protection.

### Option 1: Using phpMyAdmin (Recommended for XAMPP)

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select the `ojt_system` database from the left sidebar
3. Click on the "SQL" tab
4. Copy and paste the contents of `database/create_login_attempts_table.sql`
5. Click "Go" to execute

### Option 2: Using MySQL Command Line

```bash
# Navigate to the project directory
cd c:\xampp\htdocs\ojt-system

# Run the migration
mysql -u root -p ojt_system < database/create_login_attempts_table.sql
```

### Option 3: Using the PHP Migration Script

1. Open your browser
2. Navigate to: `http://localhost/ojt-system/database/run_migration.php`
3. The script will automatically create the table

---

## Security Features Implemented

### ✅ IMMEDIATE Priority (COMPLETED)

1. **CSRF Protection** - All forms now include CSRF tokens
2. **Brute Force Protection** - Login attempts are tracked and limited (5 attempts, 15-minute lockout)
3. **Session Security** - Enhanced with SameSite=Strict, custom session name
4. **File Upload Security** - Fixed permissions (0755), added .htaccess protection
5. **Demo Credentials Removed** - No more hardcoded credentials in login page
6. **Security Logging** - All security events are logged to `logs/security.log`

### 🔐 Security Headers

The following security headers are now automatically set on all pages:
- `X-Frame-Options: DENY` - Prevents clickjacking
- `X-Content-Type-Options: nosniff` - Prevents MIME sniffing
- `Content-Security-Policy` - Restricts resource loading
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` - Disables unnecessary browser features

### 📝 Security Logging

All security events are logged to `logs/security.log` including:
- Login attempts (success/failure)
- CSRF token failures
- File uploads
- Password changes
- Account creation

---

## Testing the Security Features

### 1. Test CSRF Protection

Try submitting a form without the CSRF token (remove it via browser dev tools):
- **Expected**: Form submission fails with "Security token validation failed"

### 2. Test Brute Force Protection

Try logging in with wrong password 5 times:
- **Expected**: Account locked for 15 minutes after 5th attempt
- **Message**: "Too many failed login attempts. Account temporarily locked. Please try again in X minutes."

### 3. Test File Upload Security

Try uploading a PHP file to the certificate upload:
- **Expected**: File rejected due to invalid file type

### 4. Check Security Logs

View the security log file:
```
logs/security.log
```

You should see entries for all security events.

---

## Configuration

### Production Deployment

When deploying to production, update `includes/config.php`:

```php
// Change this line:
define('ENVIRONMENT', 'development');

// To:
define('ENVIRONMENT', 'production');
```

This will:
- Hide error messages from users
- Disable error display
- Log all errors to files instead

### HTTPS Configuration

When you have SSL configured:

1. Update `includes/session.php` line 12:
```php
// Change from:
ini_set('session.cookie_secure', 0);

// To:
ini_set('session.cookie_secure', 1);
```

2. Update `includes/config.php` line 26:
```php
// Change from:
define('SITE_URL', 'http://localhost/ojt-system');

// To:
define('SITE_URL', 'https://yourdomain.com/ojt-system');
```

---

## Next Steps (HIGH Priority)

The following items should be completed within 1 week:

1. ✅ Convert unsafe `mysqli_query()` to prepared statements
2. ✅ Create dedicated database user with limited privileges
3. Configure HTTPS/SSL
4. Review and test all security features

---

## Support

If you encounter any issues:

1. Check `logs/security.log` for security events
2. Check `logs/error.log` for PHP errors (if exists)
3. Ensure database migration was successful
4. Verify file permissions are correct

---

## Security Checklist

- [x] CSRF tokens on all forms
- [x] Brute force protection enabled
- [x] Session security hardened
- [x] File upload permissions secured
- [x] Demo credentials removed
- [x] Security headers configured
- [x] Security logging enabled
- [ ] HTTPS configured (when ready for production)
- [ ] Database user with limited privileges created
- [ ] Production environment variable set
