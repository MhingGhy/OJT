# Configuring HTTPS for OJT System

This guide explains how to configure HTTPS/SSL for the OJT Tracking System in XAMPP.

## Prerequisites

- XAMPP installed and running
- Administrative access to your computer

---

## Step 1: Generate SSL Certificate

### Option A: Using OpenSSL (Recommended for Development)

1. Open Command Prompt as Administrator
2. Navigate to Apache bin directory:
   ```cmd
   cd C:\xampp\apache\bin
   ```

3. Generate a private key and certificate:
   ```cmd
   openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server.key -out server.crt
   ```

4. Fill in the certificate information when prompted:
   - Country Name: PH
   - State: Your State
   - Locality: Your City
   - Organization: Your Organization
   - Common Name: **localhost** (important!)
   - Email: your@email.com

5. Move the files to the correct location:
   ```cmd
   move server.key C:\xampp\apache\conf\ssl.key\
   move server.crt C:\xampp\apache\conf\ssl.crt\
   ```

---

## Step 2: Configure Apache

1. Open `C:\xampp\apache\conf\extra\httpd-ssl.conf`

2. Find and update these lines (around line 117-118):
   ```apache
   SSLCertificateFile "conf/ssl.crt/server.crt"
   SSLCertificateKeyFile "conf/ssl.key/server.key"
   ```

3. Find the `<VirtualHost _default_:443>` section and update DocumentRoot:
   ```apache
   DocumentRoot "C:/xampp/htdocs"
   ServerName localhost:443
   ```

---

## Step 3: Enable SSL Module

1. Open `C:\xampp\apache\conf\httpd.conf`

2. Find and uncomment (remove the `#`) from these lines:
   ```apache
   LoadModule ssl_module modules/mod_ssl.so
   Include conf/extra/httpd-ssl.conf
   LoadModule socache_shmcb_module modules/mod_socache_shmcb.so
   ```

---

## Step 4: Configure OJT System for HTTPS

### Update config.php

Edit `c:\xampp\htdocs\ojt-system\includes\config.php`:

```php
// Change from:
define('SITE_URL', 'http://localhost/ojt-system');

// To:
define('SITE_URL', 'https://localhost/ojt-system');
```

### Update session.php

Edit `c:\xampp\htdocs\ojt-system\includes\session.php`:

```php
// Change from:
ini_set('session.cookie_secure', 0);

// To:
ini_set('session.cookie_secure', 1);
```

---

## Step 5: Add HTTP to HTTPS Redirect

Create or edit `c:\xampp\htdocs\ojt-system\.htaccess`:

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Step 6: Restart Apache

1. Open XAMPP Control Panel
2. Stop Apache
3. Start Apache
4. Check for any errors in the Apache error log

---

## Step 7: Test HTTPS

1. Open your browser
2. Navigate to: `https://localhost/ojt-system`
3. You'll see a security warning (because it's a self-signed certificate)
4. Click "Advanced" → "Proceed to localhost (unsafe)"
5. The site should load with HTTPS

---

## Browser Security Warning

For development with self-signed certificates:

### Chrome/Edge
1. Click "Advanced"
2. Click "Proceed to localhost (unsafe)"

### Firefox
1. Click "Advanced"
2. Click "Accept the Risk and Continue"

---

## Production Deployment

For production, you should use a real SSL certificate from:

1. **Let's Encrypt** (Free)
   - Use Certbot to get free SSL certificates
   - Automatically renews

2. **Commercial SSL Providers**
   - GoDaddy
   - Namecheap
   - DigiCert

---

## Troubleshooting

### Apache won't start after enabling SSL

**Check the error log:**
```
C:\xampp\apache\logs\error.log
```

**Common issues:**
- Port 443 already in use
- Certificate files not found
- Incorrect file paths

### Port 443 already in use

1. Open Command Prompt as Administrator
2. Find what's using port 443:
   ```cmd
   netstat -ano | findstr :443
   ```
3. Stop the conflicting service or change Apache's SSL port

### Certificate errors

Make sure:
- Certificate files exist in the correct location
- File paths in `httpd-ssl.conf` are correct
- Certificate was generated for "localhost"

---

## Security Checklist After HTTPS Setup

- [x] SSL certificate installed
- [x] Apache SSL module enabled
- [x] `SITE_URL` updated to https://
- [x] `session.cookie_secure` set to 1
- [x] HTTP to HTTPS redirect enabled
- [ ] Test all functionality works over HTTPS
- [ ] Verify session cookies have Secure flag
- [ ] Check browser console for mixed content warnings

---

## Additional Security

### Enable HSTS (HTTP Strict Transport Security)

Add to `httpd-ssl.conf` inside the `<VirtualHost>` block:

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

This tells browsers to always use HTTPS for your site.

---

## Support

If you encounter issues:

1. Check Apache error logs
2. Verify certificate files exist
3. Ensure all configuration changes were saved
4. Restart Apache after any changes
5. Clear browser cache and cookies
