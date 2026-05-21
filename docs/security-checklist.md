# Student Management System Security Checklist

## Authentication Security

- Passwords are stored using PHP password hashing.
- Login checks passwords using `password_verify()`.
- Sessions are regenerated after successful login.
- Users are redirected based on their role.
- Pages are protected using role checks.

## Database Security

- PDO is used for database connection.
- Prepared statements protect against SQL injection.
- Foreign keys protect important relationships.
- User roles are stored in a separate roles table.

## Input And Output Security

- User input is validated before saving.
- Output is escaped using the `e()` helper function.
- Required fields are checked before database actions.

## Recommended Production Improvements

- Add login attempt throttling.
- Add audit logs for major actions.
- Add stronger password rules.
- Disable development error display.
- Store configuration values outside public access.
- Add email verification for password reset.

## Files To Remove Or Protect

Temporary setup files should not remain online. For example:

```text
public/reset-admin-password.php
```

This file was only used during development and should be deleted.
