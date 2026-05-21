# Student Management System

A complete web-based Student Management System built with HTML, CSS, JavaScript, PHP, and MySQL. The system supports real school workflows for administrators, teachers, and students.

## Features

- Admin, teacher, and student authentication
- Parent portal authentication and monitoring
- Password hashing and session-based access control
- Admin dashboard with statistics
- Student management
- Teacher management
- Class management
- Subject management
- Class-subject assignment
- Teacher-subject assignment
- Academic terms
- Attendance marking and review
- Result upload and student result viewing
- School fees and payment records
- Student fee status
- Parent dashboard for linked student attendance, results, fees, and insights
- Notifications
- Reports
- Search and pagination
- Change password
- Responsive user interface
- CSRF protection on major forms
- Premium SaaS dashboard UI
- Dark mode and light mode
- Chart.js analytics dashboard
- Smart simulated insights and recommendations
- Notification and profile dropdowns

## Technologies Used

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- XAMPP
- phpMyAdmin

## Local Setup

1. Install XAMPP.
2. Copy the project folder to:

```text
C:\xampp\htdocs\student-management-system
```

3. Start Apache and MySQL in XAMPP.
4. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

5. Import:

```text
database/migrations/001_create_student_management_database.sql
```

6. Import the default admin account:

```text
database/seeders/001_default_admin.sql
```

7. Open the system:

```text
http://localhost/student-management-system/public/login.php
```

## Default Admin Account

```text
Email: admin@example.com
Password: admin123
```

Change this password immediately after first login.

## Project Structure

```text
app/
  Helpers/
  Views/
config/
database/
  migrations/
  seeders/
docs/
public/
  admin/
  assets/
  student/
  teacher/
storage/
tests/
```

## Security Notes

- Passwords are hashed with PHP's password hashing API.
- Database queries use PDO prepared statements.
- Protected pages check user roles.
- Forms include CSRF protection.
- Temporary setup files should not be deployed.
- Production servers should disable visible PHP errors.

## Premium Dashboard

The admin dashboard includes Chart.js analytics for attendance, fees, and performance. It also includes simulated smart insights such as attendance health, performance prediction, and recommendations.

The interface supports light and dark mode using browser local storage.

## Documentation

Additional project documentation is available in the `docs` folder:

- Deployment guide
- Security checklist
- Common errors and solutions
- Project summary
