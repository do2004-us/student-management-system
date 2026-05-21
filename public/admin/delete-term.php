<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/terms.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid term selected.';
    redirect('/student-management-system/public/admin/terms.php');
}

try {
    $database = Connection::connect();
    $statement = $database->prepare('DELETE FROM academic_terms WHERE id = :id');
    $statement->execute(['id' => $id]);

    $_SESSION['success'] = 'Academic term deleted successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to delete term because it may be linked to results or fees.';
}

redirect('/student-management-system/public/admin/terms.php');

