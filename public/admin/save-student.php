<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/students.php');
}

$id = (int) ($_POST['id'] ?? 0);
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$admissionNumber = trim($_POST['admission_number'] ?? '');
$classId = (int) ($_POST['class_id'] ?? 0);
$gender = $_POST['gender'] ?: null;
$dateOfBirth = $_POST['date_of_birth'] ?: null;
$admissionDate = $_POST['admission_date'] ?: null;
$guardianName = trim($_POST['guardian_name'] ?? '');
$guardianPhone = trim($_POST['guardian_phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';

if ($fullName === '' || $email === '' || $admissionNumber === '' || $classId <= 0) {
    $_SESSION['error'] = 'Full name, email, admission number, and class are required.';
    redirect('/student-management-system/public/admin/students.php');
}

if ($id === 0 && $password === '') {
    $_SESSION['error'] = 'Password is required for a new student.';
    redirect('/student-management-system/public/admin/students.php');
}

try {
    $database = Connection::connect();
    $database->beginTransaction();

    $studentRole = $database->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
    $studentRole->execute(['name' => 'student']);
    $roleId = (int) $studentRole->fetchColumn();

    if ($id > 0) {
        $studentQuery = $database->prepare('SELECT user_id FROM students WHERE id = :id LIMIT 1');
        $studentQuery->execute(['id' => $id]);
        $userId = (int) $studentQuery->fetchColumn();

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

        $database->prepare($userSql)->execute($userParams);

        $studentStatement = $database->prepare(
            'UPDATE students
             SET class_id = :class_id, admission_number = :admission_number, gender = :gender,
                 date_of_birth = :date_of_birth, address = :address, guardian_name = :guardian_name,
                 guardian_phone = :guardian_phone, admission_date = :admission_date
             WHERE id = :id'
        );

        $studentStatement->execute([
            'class_id' => $classId,
            'admission_number' => $admissionNumber,
            'gender' => $gender,
            'date_of_birth' => $dateOfBirth,
            'address' => $address,
            'guardian_name' => $guardianName,
            'guardian_phone' => $guardianPhone,
            'admission_date' => $admissionDate,
            'id' => $id,
        ]);

        $_SESSION['success'] = 'Student updated successfully.';
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

        $studentStatement = $database->prepare(
            'INSERT INTO students
             (user_id, class_id, admission_number, gender, date_of_birth, address, guardian_name, guardian_phone, admission_date)
             VALUES
             (:user_id, :class_id, :admission_number, :gender, :date_of_birth, :address, :guardian_name, :guardian_phone, :admission_date)'
        );

        $studentStatement->execute([
            'user_id' => $userId,
            'class_id' => $classId,
            'admission_number' => $admissionNumber,
            'gender' => $gender,
            'date_of_birth' => $dateOfBirth,
            'address' => $address,
            'guardian_name' => $guardianName,
            'guardian_phone' => $guardianPhone,
            'admission_date' => $admissionDate,
        ]);

        $_SESSION['success'] = 'Student created successfully.';
    }

    $database->commit();
} catch (PDOException $exception) {
    if ($database->inTransaction()) {
        $database->rollBack();
    }

    $_SESSION['error'] = 'Unable to save student. Email or admission number may already exist.';
}

redirect('/student-management-system/public/admin/students.php');

