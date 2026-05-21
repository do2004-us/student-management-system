<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../app/Helpers/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/change-password.php');
}

verify_csrf_token();

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    $_SESSION['error'] = 'All password fields are required.';
    redirect('/student-management-system/public/change-password.php');
}

if (strlen($newPassword) < 6) {
    $_SESSION['error'] = 'New password must be at least 6 characters.';
    redirect('/student-management-system/public/change-password.php');
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['error'] = 'New password and confirmation do not match.';
    redirect('/student-management-system/public/change-password.php');
}

try {
    $database = Connection::connect();

    $statement = $database->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => current_user()['id']]);
    $storedPassword = $statement->fetchColumn();

    if (!$storedPassword || !password_verify($currentPassword, $storedPassword)) {
        $_SESSION['error'] = 'Current password is incorrect.';
        redirect('/student-management-system/public/change-password.php');
    }

    $update = $database->prepare('UPDATE users SET password = :password WHERE id = :id');
    $update->execute([
        'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        'id' => current_user()['id'],
    ]);

    $_SESSION['success'] = 'Password changed successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to change password. Please try again.';
}

redirect('/student-management-system/public/change-password.php');
