<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/teachers.php');
}

verify_csrf_token();

$id = (int) ($_POST['id'] ?? 0);
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$staffNumber = trim($_POST['staff_number'] ?? '');
$gender = $_POST['gender'] ?: null;
$qualification = trim($_POST['qualification'] ?? '');
$employmentDate = $_POST['employment_date'] ?: null;
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';

if ($fullName === '' || $email === '' || $staffNumber === '') {
    $_SESSION['error'] = 'Full name, email, and staff number are required.';
    redirect('/student-management-system/public/admin/teachers.php');
}

if ($id === 0 && $password === '') {
    $_SESSION['error'] = 'Password is required for a new teacher.';
    redirect('/student-management-system/public/admin/teachers.php');
}

try {
    $database = Connection::connect();
    $database->beginTransaction();

    $teacherRole = $database->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
    $teacherRole->execute(['name' => 'teacher']);
    $roleId = (int) $teacherRole->fetchColumn();

    if ($id > 0) {
        $teacherQuery = $database->prepare('SELECT user_id FROM teachers WHERE id = :id LIMIT 1');
        $teacherQuery->execute(['id' => $id]);
        $userId = (int) $teacherQuery->fetchColumn();

        $userSql = 'UPDATE users SET full_name = :full_name, email = :email, phone = :phone WHERE id = :user_id';
        $userParams = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'user_id' => $userId,
        ];

        if ($password !== '') {
            $userSql = 'UPDATE users SET full_name = :full_name, email = :email, phone = :phone, password = :password WHERE id = :user_id';
            $userParams['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userStatement = $database->prepare($userSql);
        $userStatement->execute($userParams);

        $teacherStatement = $database->prepare(
            'UPDATE teachers
             SET staff_number = :staff_number, gender = :gender, address = :address,
                 qualification = :qualification, employment_date = :employment_date
             WHERE id = :id'
        );

        $teacherStatement->execute([
            'staff_number' => $staffNumber,
            'gender' => $gender,
            'address' => $address,
            'qualification' => $qualification,
            'employment_date' => $employmentDate,
            'id' => $id,
        ]);

        $_SESSION['success'] = 'Teacher updated successfully.';
    } else {
        $userStatement = $database->prepare(
            'INSERT INTO users (role_id, full_name, email, password, phone, status)
             VALUES (:role_id, :full_name, :email, :password, :phone, :status)'
        );

        $userStatement->execute([
            'role_id' => $roleId,
            'full_name' => $fullName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => $phone,
            'status' => 'active',
        ]);

        $userId = (int) $database->lastInsertId();

        $teacherStatement = $database->prepare(
            'INSERT INTO teachers (user_id, staff_number, gender, address, qualification, employment_date)
             VALUES (:user_id, :staff_number, :gender, :address, :qualification, :employment_date)'
        );

        $teacherStatement->execute([
            'user_id' => $userId,
            'staff_number' => $staffNumber,
            'gender' => $gender,
            'address' => $address,
            'qualification' => $qualification,
            'employment_date' => $employmentDate,
        ]);

        $_SESSION['success'] = 'Teacher created successfully.';
    }

    $database->commit();
} catch (PDOException $exception) {
    if ($database->inTransaction()) {
        $database->rollBack();
    }

    $_SESSION['error'] = 'Unable to save teacher. Email or staff number may already exist.';
}

redirect('/student-management-system/public/admin/teachers.php');
