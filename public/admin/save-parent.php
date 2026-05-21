<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/parents.php');
}

verify_csrf_token();

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$studentId = (int) ($_POST['student_id'] ?? 0);
$relationship = trim($_POST['relationship'] ?? 'Guardian');
$occupation = trim($_POST['occupation'] ?? '');
$address = trim($_POST['address'] ?? '');

if ($fullName === '' || $email === '' || $password === '' || $studentId <= 0) {
    $_SESSION['error'] = 'Full name, email, password, and linked student are required.';
    redirect('/student-management-system/public/admin/parents.php');
}

try {
    $database = Connection::connect();
    $database->beginTransaction();

    $roleStatement = $database->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
    $roleStatement->execute(['name' => 'parent']);
    $roleId = (int) $roleStatement->fetchColumn();

    if ($roleId <= 0) {
        throw new RuntimeException('Parent role does not exist. Import parent_portal_update.sql first.');
    }

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

    $parentStatement = $database->prepare(
        'INSERT INTO parents (user_id, occupation, address)
         VALUES (:user_id, :occupation, :address)'
    );
    $parentStatement->execute([
        'user_id' => $userId,
        'occupation' => $occupation,
        'address' => $address,
    ]);

    $parentId = (int) $database->lastInsertId();

    $linkStatement = $database->prepare(
        'INSERT INTO parent_students (parent_id, student_id, relationship)
         VALUES (:parent_id, :student_id, :relationship)'
    );
    $linkStatement->execute([
        'parent_id' => $parentId,
        'student_id' => $studentId,
        'relationship' => $relationship,
    ]);

    $database->commit();
    $_SESSION['success'] = 'Parent account created and linked successfully.';
} catch (Throwable $exception) {
    if (isset($database) && $database->inTransaction()) {
        $database->rollBack();
    }

    $_SESSION['error'] = 'Unable to save parent. Email may already exist, or parent database update is missing.';
}

redirect('/student-management-system/public/admin/parents.php');

