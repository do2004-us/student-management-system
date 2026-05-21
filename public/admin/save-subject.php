<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/subjects.php');
}

$id = (int) ($_POST['id'] ?? 0);
$subjectName = trim($_POST['subject_name'] ?? '');
$subjectCode = strtoupper(trim($_POST['subject_code'] ?? ''));

if ($subjectName === '' || $subjectCode === '') {
    $_SESSION['error'] = 'Subject name and subject code are required.';
    redirect('/student-management-system/public/admin/subjects.php');
}

try {
    $database = Connection::connect();

    if ($id > 0) {
        $statement = $database->prepare(
            'UPDATE subjects
             SET subject_name = :subject_name, subject_code = :subject_code
             WHERE id = :id'
        );

        $statement->execute([
            'subject_name' => $subjectName,
            'subject_code' => $subjectCode,
            'id' => $id,
        ]);

        $_SESSION['success'] = 'Subject updated successfully.';
    } else {
        $statement = $database->prepare(
            'INSERT INTO subjects (subject_name, subject_code)
             VALUES (:subject_name, :subject_code)'
        );

        $statement->execute([
            'subject_name' => $subjectName,
            'subject_code' => $subjectCode,
        ]);

        $_SESSION['success'] = 'Subject created successfully.';
    }
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to save subject. The subject name or code may already exist.';
}

redirect('/student-management-system/public/admin/subjects.php');

