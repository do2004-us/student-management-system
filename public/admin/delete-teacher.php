<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/teachers.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid teacher selected.';
    redirect('/student-management-system/public/admin/teachers.php');
}

try {
    $database = Connection::connect();

    $statement = $database->prepare('SELECT user_id FROM teachers WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $userId = (int) $statement->fetchColumn();

    $deleteUser = $database->prepare('DELETE FROM users WHERE id = :user_id');
    $deleteUser->execute(['user_id' => $userId]);

    $_SESSION['success'] = 'Teacher deleted successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to delete teacher because the teacher may be linked to records.';
}

redirect('/student-management-system/public/admin/teachers.php');

