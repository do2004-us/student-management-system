<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/terms.php');
}

verify_csrf_token();

$id = (int) ($_POST['id'] ?? 0);
$termName = trim($_POST['term_name'] ?? '');
$academicYear = trim($_POST['academic_year'] ?? '');
$startDate = $_POST['start_date'] ?: null;
$endDate = $_POST['end_date'] ?: null;
$isActive = isset($_POST['is_active']) ? 1 : 0;

if ($termName === '' || $academicYear === '') {
    $_SESSION['error'] = 'Term name and academic year are required.';
    redirect('/student-management-system/public/admin/terms.php');
}

try {
    $database = Connection::connect();
    $database->beginTransaction();

    if ($isActive === 1) {
        $database->exec('UPDATE academic_terms SET is_active = 0');
    }

    if ($id > 0) {
        $statement = $database->prepare(
            'UPDATE academic_terms
             SET term_name = :term_name, academic_year = :academic_year,
                 start_date = :start_date, end_date = :end_date, is_active = :is_active
             WHERE id = :id'
        );

        $statement->execute([
            'term_name' => $termName,
            'academic_year' => $academicYear,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $isActive,
            'id' => $id,
        ]);

        $_SESSION['success'] = 'Academic term updated successfully.';
    } else {
        $statement = $database->prepare(
            'INSERT INTO academic_terms (term_name, academic_year, start_date, end_date, is_active)
             VALUES (:term_name, :academic_year, :start_date, :end_date, :is_active)'
        );

        $statement->execute([
            'term_name' => $termName,
            'academic_year' => $academicYear,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $isActive,
        ]);

        $_SESSION['success'] = 'Academic term created successfully.';
    }

    $database->commit();
} catch (PDOException $exception) {
    if ($database->inTransaction()) {
        $database->rollBack();
    }

    $_SESSION['error'] = 'Unable to save academic term. It may already exist.';
}

redirect('/student-management-system/public/admin/terms.php');
