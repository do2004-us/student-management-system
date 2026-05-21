<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/class-subjects.php');
}

verify_csrf_token();

$classId = (int) ($_POST['class_id'] ?? 0);
$subjectId = (int) ($_POST['subject_id'] ?? 0);

if ($classId <= 0 || $subjectId <= 0) {
    $_SESSION['error'] = 'Please select both class and subject.';
    redirect('/student-management-system/public/admin/class-subjects.php');
}

try {
    $database = Connection::connect();
    $statement = $database->prepare(
        'INSERT INTO class_subjects (class_id, subject_id)
         VALUES (:class_id, :subject_id)'
    );

    $statement->execute([
        'class_id' => $classId,
        'subject_id' => $subjectId,
    ]);

    $_SESSION['success'] = 'Subject assigned to class successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to assign subject. This subject may already be assigned to the class.';
}

redirect('/student-management-system/public/admin/class-subjects.php');
