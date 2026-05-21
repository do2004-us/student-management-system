<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/classes.php');
}

$id = (int) ($_POST['id'] ?? 0);
$className = trim($_POST['class_name'] ?? '');

if ($className === '') {
    $_SESSION['error'] = 'Class name is required.';
    redirect('/student-management-system/public/admin/classes.php');
}

try {
    $database = Connection::connect();

    if ($id > 0) {
        $statement = $database->prepare(
            'UPDATE classes SET class_name = :class_name WHERE id = :id'
        );

        $statement->execute([
            'class_name' => $className,
            'id' => $id,
        ]);

        $_SESSION['success'] = 'Class updated successfully.';
    } else {
        $statement = $database->prepare(
            'INSERT INTO classes (class_name) VALUES (:class_name)'
        );

        $statement->execute(['class_name' => $className]);
        $_SESSION['success'] = 'Class created successfully.';
    }
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to save class. The class may already exist.';
}

redirect('/student-management-system/public/admin/classes.php');

