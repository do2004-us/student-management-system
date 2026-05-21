<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/fees.php');
}

verify_csrf_token();

$studentId = (int) ($_POST['student_id'] ?? 0);
$termId = (int) ($_POST['term_id'] ?? 0);
$amountDue = (float) ($_POST['amount_due'] ?? 0);
$dueDate = $_POST['due_date'] ?: null;

if ($studentId <= 0 || $termId <= 0 || $amountDue <= 0) {
    $_SESSION['error'] = 'Student, term, and amount due are required.';
    redirect('/student-management-system/public/admin/fees.php');
}

try {
    $database = Connection::connect();
    $statement = $database->prepare(
        'INSERT INTO fees (student_id, term_id, amount_due, due_date, status)
         VALUES (:student_id, :term_id, :amount_due, :due_date, :status)'
    );

    $statement->execute([
        'student_id' => $studentId,
        'term_id' => $termId,
        'amount_due' => $amountDue,
        'due_date' => $dueDate,
        'status' => 'unpaid',
    ]);

    $_SESSION['success'] = 'Fee assigned successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to assign fee. Please try again.';
}

redirect('/student-management-system/public/admin/fees.php');
