# Common Errors And Solutions

## 1. Not Found Error

Cause:
The requested file is not inside the XAMPP `htdocs` project folder.

Solution:
Make sure the project is located at:

```text
C:\xampp\htdocs\student-management-system
```

Then visit:

```text
http://localhost/student-management-system/public/login.php
```

## 2. Database Connection Failed

Cause:
MySQL may not be running, or the database name may be wrong.

Solution:

- Start MySQL in XAMPP.
- Confirm the database exists in phpMyAdmin.
- Check `config/database_config.php`.

## 3. Invalid Email Or Password

Cause:
The user does not exist, or the password hash does not match.

Solution:

- Confirm the user exists in the `users` table.
- Reset the password through the system or recreate the account.

## 4. Cannot Declare Class Error

Cause:
Two PHP files may have similar names, especially on Windows.

Solution:
Use clear file names such as:

```text
Connection.php
database_config.php
```

Avoid using both `Database.php` and `database.php`.

## 5. Duplicate Entry Error

Cause:
A unique value already exists, such as email, staff number, admission number, class name, or subject code.

Solution:
Use a different unique value.

