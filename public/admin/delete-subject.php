<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/subjects.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid subject selected.';
    redirect('/student-management-system/public/admin/subjects.php');
}

try {
    $database = Connection::connect();
    $statement = $database->prepare('DELETE FROM subjects WHERE id = :id');
    $statement->execute(['id' => $id]);

    $_SESSION['success'] = 'Subject deleted successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to delete subject because it may be linked to classes, teachers, or results.';
}

redirect('/student-management-system/public/admin/subjects.php');

