<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/teacher-subjects.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid teacher assignment selected.';
    redirect('/student-management-system/public/admin/teacher-subjects.php');
}

try {
    $database = Connection::connect();
    $statement = $database->prepare('DELETE FROM teacher_subjects WHERE id = :id');
    $statement->execute(['id' => $id]);

    $_SESSION['success'] = 'Teacher assignment removed successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to remove teacher assignment.';
}

redirect('/student-management-system/public/admin/teacher-subjects.php');

