<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('student');

$database = Connection::connect();

$studentStatement = $database->prepare('SELECT id FROM students WHERE user_id = :user_id LIMIT 1');
$studentStatement->execute(['user_id' => current_user()['id']]);
$studentId = (int) $studentStatement->fetchColumn();

$statement = $database->prepare(
    'SELECT fees.*, academic_terms.term_name, academic_terms.academic_year,
            COALESCE(SUM(payments.amount_paid), 0) AS paid_amount
     FROM fees
     INNER JOIN academic_terms ON academic_terms.id = fees.term_id
     LEFT JOIN payments ON payments.fee_id = fees.id
     WHERE fees.student_id = :student_id
     GROUP BY fees.id
     ORDER BY fees.created_at DESC'
);
$statement->execute(['student_id' => $studentId]);
$fees = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fees - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Student Portal</p>
                <h1>My Fees</h1>
                <p>View assigned fees and payment status.</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-light">Dashboard</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </header>

        <section class="content-panel">
            <h2>Fee Records</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Term</th>
                            <th>Amount Due</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$fees): ?>
                            <tr>
                                <td colspan="7">No fee records found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($fees as $index => $fee): ?>
                            <?php $balance = (float) $fee['amount_due'] - (float) $fee['paid_amount']; ?>
                            <tr>
                                <td><?= e((string) ($index + 1)); ?></td>
                                <td><?= e($fee['term_name'] . ' ' . $fee['academic_year']); ?></td>
                                <td><?= e(number_format((float) $fee['amount_due'], 2)); ?></td>
                                <td><?= e(number_format((float) $fee['paid_amount'], 2)); ?></td>
                                <td><?= e(number_format(max(0, $balance), 2)); ?></td>
                                <td><?= e($fee['due_date'] ?? ''); ?></td>
                                <td><?= e(ucfirst(str_replace('_', ' ', $fee['status']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>

