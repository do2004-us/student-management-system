<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$feeId = (int) ($_GET['fee_id'] ?? 0);

$feeStatement = $database->prepare(
    'SELECT fees.*, users.full_name, students.id AS student_id,
            academic_terms.term_name, academic_terms.academic_year
     FROM fees
     INNER JOIN students ON students.id = fees.student_id
     INNER JOIN users ON users.id = students.user_id
     INNER JOIN academic_terms ON academic_terms.id = fees.term_id
     WHERE fees.id = :fee_id
     LIMIT 1'
);
$feeStatement->execute(['fee_id' => $feeId]);
$fee = $feeStatement->fetch();

if (!$fee) {
    $_SESSION['error'] = 'Fee record not found.';
    redirect('/student-management-system/public/admin/fees.php');
}

$paymentStatement = $database->prepare('SELECT * FROM payments WHERE fee_id = :fee_id ORDER BY payment_date DESC');
$paymentStatement->execute(['fee_id' => $feeId]);
$payments = $paymentStatement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Admin Portal</p>
                <h1>Record Payment</h1>
                <p><?= e($fee['full_name'] . ' - ' . $fee['term_name'] . ' ' . $fee['academic_year']); ?></p>
            </div>
            <div class="header-actions">
                <a href="fees.php" class="btn btn-light">Back to Fees</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error); ?></div>
        <?php endif; ?>

        <section class="management-grid">
            <article class="content-panel">
                <h2>Add Payment</h2>
                <form action="save-payment.php" method="POST" class="stack-form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="fee_id" value="<?= e((string) $fee['id']); ?>">
                    <input type="hidden" name="student_id" value="<?= e((string) $fee['student_id']); ?>">

                    <div class="form-group">
                        <label for="amount_paid">Amount Paid</label>
                        <input type="number" id="amount_paid" name="amount_paid" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number">
                    </div>

                    <div class="form-group">
                        <label for="payment_date">Payment Date</label>
                        <input type="date" id="payment_date" name="payment_date" value="<?= e(date('Y-m-d')); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </form>
            </article>

            <article class="content-panel">
                <h2>Payment History</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$payments): ?>
                                <tr>
                                    <td colspan="5">No payments recorded.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($payments as $index => $payment): ?>
                                <tr>
                                    <td><?= e((string) ($index + 1)); ?></td>
                                    <td><?= e($payment['payment_date']); ?></td>
                                    <td><?= e(number_format((float) $payment['amount_paid'], 2)); ?></td>
                                    <td><?= e(ucfirst(str_replace('_', ' ', $payment['payment_method']))); ?></td>
                                    <td><?= e($payment['reference_number'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </main>
</body>
</html>
