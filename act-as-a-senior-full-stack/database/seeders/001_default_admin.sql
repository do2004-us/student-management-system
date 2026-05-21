-- Default admin account for first login.
-- Email: admin@example.com
-- Password: admin123

USE student_management_system;

INSERT INTO users (role_id, full_name, email, password, phone, status)
SELECT
  roles.id,
  'System Administrator',
  'admin@example.com',
  '$2y$10$sPE9bclNgq4nRxSofpKpcOFm6Z9D2cY1P5l0Uq0sg6coGvKlAgOEq',
  '0000000000',
  'active'
FROM roles
WHERE roles.name = 'admin'
  AND NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@example.com'
  );

