# OJT Information and Tracking System

A complete web-based On-the-Job Training (OJT) management system built with PHP, MySQL, HTML, CSS, and JavaScript. This system helps organizations track trainee information, manage training records, and monitor progress.

## 🌟 Features

### For Trainees (Users)
- **Personal Dashboard** - View training statistics and progress
- **Profile Management** - View demographic and educational information
- **Training History** - Complete timeline of all training records
- **Certificate Access** - View and download training certificates

### For Administrators
- **Analytics Dashboard** - Visual charts showing gender distribution and training status
- **Trainee Management** - Full CRUD operations (Create, Read, Update, Delete)
- **Certificate Upload** - Secure file upload for PDF and image certificates
- **Advanced Search** - Find qualified trainees by multiple criteria:
  - Name, email, course, or school
  - Training type and status
  - Completion date range
  - Perfect for screening candidates for job placements

## 🛠️ Technology Stack

- **Backend**: PHP (Procedural)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: Vanilla JS
- **Charts**: Chart.js (included locally)
- **Server**: XAMPP (Apache + MySQL)

## 📋 Prerequisites

- XAMPP (or similar LAMP/WAMP stack)
- Web browser (Chrome, Firefox, Edge)
- Text editor (optional, for modifications)

## 🚀 Installation Instructions

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP to `C:\xampp` (default location)
3. Start **Apache** and **MySQL** from XAMPP Control Panel

### Step 2: Copy Project Files

1. Copy the entire `ojt-system` folder to `C:\xampp\htdocs\`
2. Final path should be: `C:\xampp\htdocs\ojt-system\`

### Step 3: Create Database

**Option A: Using phpMyAdmin (Recommended)**

1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click on "Import" tab
3. Click "Choose File" and select `ojt-system/database/ojt_system.sql`
4. Click "Go" button at the bottom
5. Wait for success message

**Option B: Using MySQL Command Line**

```bash
mysql -u root -p < C:\xampp\htdocs\ojt-system\database\ojt_system.sql
```

### Step 4: Configure Database Connection (Optional)

If you have a MySQL password set, edit `includes/config.php`:

```php
define('DB_PASS', 'your_password_here');
```

### Step 5: Set Folder Permissions

Ensure the `uploads/certificates/` folder is writable:
- Right-click on `ojt-system/uploads/certificates/`
- Properties → Security → Edit
- Give "Full Control" to Users

### Step 6: Access the System

Open your browser and navigate to:
```
http://localhost/ojt-system
```

## 🔐 Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`

### Sample Trainee Account
- **Username**: `juan.delacruz`
- **Password**: `password123`

> **⚠️ Important**: Change these passwords after first login in a production environment!

## 📁 Project Structure

```
ojt-system/
├── admin/                      # Admin module
│   ├── index.php              # Admin dashboard
│   ├── trainees.php           # Manage trainees
│   ├── add_trainee.php        # Add new trainee
│   ├── edit_trainee.php       # Edit trainee
│   ├── delete_trainee.php     # Delete trainee
│   ├── upload_certificate.php # Upload certificates
│   └── search.php             # Advanced search
├── user/                       # User module
│   ├── index.php              # User dashboard
│   ├── profile.php            # User profile
│   └── certificates.php       # View certificates
├── includes/                   # Core files
│   ├── config.php             # Database configuration
│   ├── functions.php          # Helper functions
│   └── session.php            # Session management
├── assets/                     # Static assets
│   ├── css/
│   │   └── style.css          # Custom styles
│   └── js/
│       └── main.js            # JavaScript utilities
├── uploads/                    # Uploaded files
│   └── certificates/          # Certificate storage
├── database/                   # Database files
│   └── ojt_system.sql         # Database schema
├── index.php                   # Landing page
├── login.php                   # Login page
├── logout.php                  # Logout handler
└── README.md                   # This file
```

## 📊 Database Schema

### Tables

1. **users** - User accounts (admin and trainees)
2. **trainees** - Trainee demographic information
3. **training_records** - Training history and progress
4. **certificates** - Uploaded certificate files

### Relationships

- `users.trainee_id` → `trainees.id`
- `training_records.trainee_id` → `trainees.id`
- `certificates.trainee_id` → `trainees.id`
- `certificates.training_record_id` → `training_records.id`

## 🔒 Security Features

- **Password Hashing** - Using PHP's `password_hash()` with bcrypt
- **Prepared Statements** - All database queries use prepared statements
- **Input Sanitization** - All user inputs are sanitized
- **File Upload Validation** - MIME type and extension checking
- **Session Security** - HTTP-only cookies and session regeneration
- **SQL Injection Protection** - Parameterized queries throughout

## 🎨 Features Highlights

### Analytics Dashboard
- Real-time statistics
- Gender distribution pie chart
- Training status bar chart
- Recent trainees list

### Search Functionality
- Multi-criteria search
- Filter by training status (Completed/Ongoing)
- Filter by training type
- Date range filtering
- Export-ready results

### Certificate Management
- Support for PDF, JPG, PNG formats
- 5MB file size limit
- Secure file naming
- Preview for images
- Download functionality

## 🐛 Troubleshooting

### Database Connection Error
- Verify MySQL is running in XAMPP
- Check database credentials in `includes/config.php`
- Ensure database `ojt_system` exists

### File Upload Errors
- Check folder permissions on `uploads/certificates/`
- Verify file size is under 5MB
- Ensure file type is PDF, JPG, or PNG

### Page Not Found (404)
- Verify project is in `htdocs/ojt-system/`
- Check Apache is running in XAMPP
- Clear browser cache

### Charts Not Displaying
- Check browser console for JavaScript errors
- Verify Chart.js is loading from CDN
- Ensure internet connection is active

## 📝 Usage Guide

### Adding a New Trainee (Admin)

1. Login as admin
2. Click "Add New Trainee" button
3. Fill in personal information (required fields marked with *)
4. Fill in educational information
5. Optionally add training information
6. Click "Add Trainee"
7. Note the generated username and password

### Uploading Certificates (Admin)

1. Go to "Manage Trainees"
2. Click upload icon next to trainee name
3. Enter certificate name
4. Select related training record
5. Choose file (PDF/JPG/PNG, max 5MB)
6. Click "Upload Certificate"

### Searching for Qualified Trainees (Admin)

1. Go to "Search Trainees"
2. Enter search criteria:
   - Name, email, course, or school
   - Training type
   - Training status
   - Date range
3. Click "Search"
4. View results with training details

## 🔄 Future Enhancements

- Email notifications for certificate uploads
- Bulk import/export functionality
- Training calendar view
- Performance reports
- Mobile app integration
- Multi-language support

## 📄 License

This project is open-source and available for educational and commercial use.

## 👥 Support

For issues, questions, or contributions:
- Check the troubleshooting section
- Review the code comments
- Modify as needed for your requirements

## 🎯 System Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Apache**: 2.4 or higher
- **Browser**: Modern browser with JavaScript enabled
- **Disk Space**: Minimum 50MB (more for certificates)

---

**Built with ❤️ for efficient OJT management**

Last Updated: January 2026
