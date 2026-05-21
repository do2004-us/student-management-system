<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/teacher-subjects.php');
}

$teacherId = (int) ($_POST['teacher_id'] ?? 0);
$classId = (int) ($_POST['class_id'] ?? 0);
$subjectId = (int) ($_POST['subject_id'] ?? 0);

if ($teacherId <= 0 || $classId <= 0 || $subjectId <= 0) {
    $_SESSION['error'] = 'Please select teacher, class, and subject.';
    redirect('/student-management-system/public/admin/teacher-subjects.php');
}

try {
    $database = Connection::connect();
    $statement = $database->prepare(
        'INSERT INTO teacher_subjects (teacher_id, subject_id, class_id)
         VALUES (:teacher_id, :subject_id, :class_id)'
    );

    $statement->execute([
        'teacher_id' => $teacherId,
        'subject_id' => $subjectId,
        'class_id' => $classId,
    ]);

    $_SESSION['success'] = 'Teacher assigned successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to assign teacher. This assignment may already exist.';
}

redirect('/student-management-system/public/admin/teacher-subjects.php');

