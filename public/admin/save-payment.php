<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/fees.php');
}

$feeId = (int) ($_POST['fee_id'] ?? 0);
$studentId = (int) ($_POST['student_id'] ?? 0);
$amountPaid = (float) ($_POST['amount_paid'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? 'cash';
$referenceNumber = trim($_POST['reference_number'] ?? '');
$paymentDate = $_POST['payment_date'] ?? date('Y-m-d');

if ($feeId <= 0 || $studentId <= 0 || $amountPaid <= 0) {
    $_SESSION['error'] = 'Amount paid is required.';
    redirect('/student-management-system/public/admin/fees.php');
}

try {
    $database = Connection::connect();
    $database->beginTransaction();

    $payment = $database->prepare(
        'INSERT INTO payments (fee_id, student_id, amount_paid, payment_method, reference_number, payment_date, received_by)
         VALUES (:fee_id, :student_id, :amount_paid, :payment_method, :reference_number, :payment_date, :received_by)'
    );

    $payment->execute([
        'fee_id' => $feeId,
        'student_id' => $studentId,
        'amount_paid' => $amountPaid,
        'payment_method' => $paymentMethod,
        'reference_number' => $referenceNumber,
        'payment_date' => $paymentDate,
        'received_by' => current_user()['id'],
    ]);

    $summary = $database->prepare(
        'SELECT fees.amount_due, COALESCE(SUM(payments.amount_paid), 0) AS paid_amount
         FROM fees
         LEFT JOIN payments ON payments.fee_id = fees.id
         WHERE fees.id = :fee_id
         GROUP BY fees.id'
    );
    $summary->execute(['fee_id' => $feeId]);
    $fee = $summary->fetch();

    $status = 'unpaid';
    if ((float) $fee['paid_amount'] >= (float) $fee['amount_due']) {
        $status = 'paid';
    } elseif ((float) $fee['paid_amount'] > 0) {
        $status = 'partly_paid';
    }

    $updateFee = $database->prepare('UPDATE fees SET status = :status WHERE id = :fee_id');
    $updateFee->execute([
        'status' => $status,
        'fee_id' => $feeId,
    ]);

    $database->commit();
    $_SESSION['success'] = 'Payment recorded successfully.';
} catch (PDOException $exception) {
    if ($database->inTransaction()) {
        $database->rollBack();
    }

    $_SESSION['error'] = 'Unable to record payment.';
}

redirect('/student-management-system/public/admin/payments.php?fee_id=' . $feeId);

