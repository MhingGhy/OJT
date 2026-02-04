# OJT System - Quick Setup Guide

## Prerequisites
- XAMPP installed on your system
- Web browser (Chrome, Firefox, or Edge)

## Installation Steps

### 1. Start XAMPP Services
1. Open XAMPP Control Panel
2. Click "Start" for **Apache**
3. Click "Start" for **MySQL**
4. Wait for both to show green "Running" status

### 2. Copy Project Files
```
Copy the 'ojt-system' folder to:
C:\xampp\htdocs\

Final location: C:\xampp\htdocs\ojt-system\
```

### 3. Import Database

**Using phpMyAdmin:**
1. Open browser: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Click "Choose File"
4. Select: `ojt-system/database/ojt_system.sql`
5. Click "Go" button
6. Wait for "Import has been successfully finished" message

**Using Command Line:**
```bash
cd C:\xampp\mysql\bin
mysql -u root -p
# Press Enter (no password by default)
source C:/xampp/htdocs/ojt-system/database/ojt_system.sql
exit
```

### 4. Set Folder Permissions (Windows)
1. Navigate to: `C:\xampp\htdocs\ojt-system\uploads\certificates\`
2. Right-click folder → Properties
3. Security tab → Edit
4. Select "Users" → Check "Full Control"
5. Click OK

### 5. Access the System
Open your browser and go to:
```
http://localhost/ojt-system
```

## Default Login Credentials

### Admin Login
```
URL: http://localhost/ojt-system/login.php?admin=1
Username: admin
Password: admin123
```

### Trainee Login
```
URL: http://localhost/ojt-system/login.php
Username: juan.delacruz
Password: password123
```

## Verification Checklist

✅ Apache is running (green in XAMPP)
✅ MySQL is running (green in XAMPP)
✅ Database imported successfully
✅ Can access landing page
✅ Can login as admin
✅ Can login as trainee
✅ Charts display on admin dashboard
✅ Can upload certificates

## Common Issues & Solutions

### Issue: "Connection failed"
**Solution:** 
- Ensure MySQL is running in XAMPP
- Check if database 'ojt_system' exists in phpMyAdmin

### Issue: "Page not found"
**Solution:**
- Verify project is in `C:\xampp\htdocs\ojt-system\`
- Check Apache is running
- Try: `http://localhost/ojt-system/index.php`

### Issue: "Permission denied" when uploading
**Solution:**
- Set write permissions on `uploads/certificates/` folder
- On Windows: Right-click → Properties → Security → Full Control

### Issue: Charts not showing
**Solution:**
- Check internet connection (Chart.js loads from CDN)
- Check browser console for errors (F12)
- Try different browser

## Configuration (Optional)

### Change Database Password
Edit: `includes/config.php`
```php
define('DB_PASS', 'your_password_here');
```

### Change Upload Limits
Edit: `includes/config.php`
```php
define('MAX_FILE_SIZE', 10485760); // 10MB
```

Also edit: `C:\xampp\php\php.ini`
```ini
upload_max_filesize = 10M
post_max_size = 10M
```
Restart Apache after changes.

### Change Timezone
Edit: `includes/config.php`
```php
date_default_timezone_set('America/New_York');
```

## Testing the System

### Test Admin Functions
1. Login as admin
2. Add a new trainee
3. Edit trainee information
4. Upload a certificate
5. Search for trainees
6. View analytics dashboard

### Test User Functions
1. Login as trainee
2. View dashboard
3. Check profile information
4. View training history
5. Download certificates

## Sample Data Included

The system comes with:
- 1 admin account
- 4 sample trainees
- 4 training records
- Sample demographic data

You can delete or modify this data as needed.

## Next Steps

1. **Change Default Passwords**
   - Login as admin
   - Update admin password
   - Update sample trainee passwords

2. **Add Real Data**
   - Delete sample trainees (optional)
   - Add your actual trainees
   - Upload real certificates

3. **Customize**
   - Update site name in `includes/config.php`
   - Modify colors in `assets/css/style.css`
   - Add your organization logo

## Backup Instructions

### Backup Database
```
phpMyAdmin → Export → ojt_system → Go
Save the .sql file
```

### Backup Files
```
Copy entire 'ojt-system' folder
Especially important: uploads/certificates/
```

## Production Deployment

⚠️ **Before deploying to production:**

1. Change all default passwords
2. Set strong database password
3. Enable HTTPS (SSL certificate)
4. Update `SITE_URL` in config.php
5. Disable error display in php.ini
6. Set proper file permissions (755 for folders, 644 for files)
7. Regular backups schedule

## Support & Documentation

- Full README: `README.md`
- Database Schema: `database/ojt_system.sql`
- Code Comments: Throughout all PHP files

## System Information

- **Version**: 1.0
- **Release Date**: January 2026
- **PHP Version Required**: 7.4+
- **MySQL Version Required**: 5.7+

---

**Need Help?**
- Check README.md for detailed documentation
- Review troubleshooting section
- Check XAMPP documentation

**Ready to use!** 🎉
