# Student Management System Deployment Guide

## 1. Local Development Setup

The system was developed using XAMPP, PHP, MySQL, HTML, CSS, and JavaScript.

Required software:

- XAMPP for Apache, PHP, and MySQL
- Visual Studio Code for editing source code
- Microsoft Edge or another browser for testing
- phpMyAdmin for database management
- Git for version control

## 2. Local Project Location

For XAMPP, the project should be placed in:

```text
C:\xampp\htdocs\student-management-system
```

The browser URL is:

```text
http://localhost/student-management-system/public
```

## 3. Database Setup

1. Open XAMPP.
2. Start Apache and MySQL.
3. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Import the database file:

```text
database/migrations/001_create_student_management_database.sql
```

5. Import the default admin file:

```text
database/seeders/001_default_admin.sql
```

Default admin login:

```text
Email: admin@example.com
Password: admin123
```

Change the default password immediately after first login.

## 4. Hosting Requirements

Recommended hosting features:

- PHP 8.1 or higher
- MySQL 5.7 or higher, preferably MySQL 8
- Apache or Nginx web server
- SSL certificate
- phpMyAdmin or database access
- File manager or FTP access

## 5. Online Deployment Steps

1. Buy hosting that supports PHP and MySQL.
2. Upload the project files to the hosting file manager.
3. Point the domain to the hosting server.
4. Create a MySQL database on the hosting control panel.
5. Import the SQL database file.
6. Update database connection settings in:

```text
config/database_config.php
```

7. Set the public folder as the web root if the hosting provider allows it.
8. Enable HTTPS using SSL.
9. Test admin, teacher, and student login.

## 6. Production Security Checklist

- Change the default admin password.
- Disable visible PHP errors in production.
- Use HTTPS.
- Use strong database passwords.
- Do not expose private folders publicly.
- Back up the database regularly.
- Remove temporary setup files.
- Use secure file permissions.

