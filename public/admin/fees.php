<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$students = $database
    ->query('SELECT students.id, students.admission_number, users.full_name, classes.class_name
             FROM students
             INNER JOIN users ON users.id = students.user_id
             INNER JOIN classes ON classes.id = students.class_id
             ORDER BY users.full_name ASC')
    ->fetchAll();

$terms = $database->query('SELECT * FROM academic_terms ORDER BY id DESC')->fetchAll();

$fees = $database
    ->query('SELECT fees.*, users.full_name, students.admission_number,
                    academic_terms.term_name, academic_terms.academic_year,
                    COALESCE(SUM(payments.amount_paid), 0) AS paid_amount
             FROM fees
             INNER JOIN students ON students.id = fees.student_id
             INNER JOIN users ON users.id = students.user_id
             INNER JOIN academic_terms ON academic_terms.id = fees.term_id
             LEFT JOIN payments ON payments.fee_id = fees.id
             GROUP BY fees.id
             ORDER BY fees.created_at DESC')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fees - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <span class="brand-mark">SMS</span>
                <strong>School Admin</strong>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="students.php">Students</a>
                <a href="teachers.php">Teachers</a>
                <a href="classes.php">Classes</a>
                <a href="subjects.php">Subjects</a>
                <a href="class-subjects.php">Class Subjects</a>
                <a href="teacher-subjects.php">Teacher Subjects</a>
                <a href="terms.php">Terms</a>
                <a href="#">Attendance</a>
                <a href="#">Results</a>
                <a href="fees.php" class="active">Fees</a>
                <a href="reports.php">Reports</a>
            </nav>
        </aside>

        <section class="main-area">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Manage Fees</h1>
                </div>
                <div class="topbar-user">
                    <span><?= e(current_user()['full_name']); ?></span>
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
                    <h2>Assign Fee</h2>
                    <form action="save-fee.php" method="POST" class="stack-form">
                        <div class="form-group">
                            <label for="student_id">Student</label>
                            <select id="student_id" name="student_id" required>
                                <option value="">Select student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= e((string) $student['id']); ?>">
                                        <?= e($student['full_name'] . ' - ' . $student['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="term_id">Term</label>
                            <select id="term_id" name="term_id" required>
                                <option value="">Select term</option>
                                <?php foreach ($terms as $term): ?>
                                    <option value="<?= e((string) $term['id']); ?>">
                                        <?= e($term['term_name'] . ' ' . $term['academic_year']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="amount_due">Amount Due</label>
                            <input type="number" id="amount_due" name="amount_due" min="0" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" id="due_date" name="due_date">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Fee</button>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Fee Records</h2>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Term</th>
                                    <th>Due</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$fees): ?>
                                    <tr>
                                        <td colspan="7">No fee records found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($fees as $index => $fee): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($fee['full_name']); ?></td>
                                        <td><?= e($fee['term_name'] . ' ' . $fee['academic_year']); ?></td>
                                        <td><?= e(number_format((float) $fee['amount_due'], 2)); ?></td>
                                        <td><?= e(number_format((float) $fee['paid_amount'], 2)); ?></td>
                                        <td><?= e(ucfirst(str_replace('_', ' ', $fee['status']))); ?></td>
                                        <td class="table-actions">
                                            <a href="payments.php?fee_id=<?= e((string) $fee['id']); ?>">Payment</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </section>
    </main>
</body>
</html>
