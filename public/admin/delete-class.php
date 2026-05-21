<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/classes.php');
}

verify_csrf_token();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid class selected.';
    redirect('/student-management-system/public/admin/classes.php');
}

try {
    $database = Connection::connect();
    $statement = $database->prepare('DELETE FROM classes WHERE id = :id');
    $statement->execute(['id' => $id]);

    $_SESSION['success'] = 'Class deleted successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to delete class because it may be linked to students or records.';
}

redirect('/student-management-system/public/admin/classes.php');
