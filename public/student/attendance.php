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
    'SELECT attendance.*, classes.class_name, users.full_name AS marked_by_name
     FROM attendance
     INNER JOIN classes ON classes.id = attendance.class_id
     INNER JOIN users ON users.id = attendance.marked_by
     WHERE attendance.student_id = :student_id
     ORDER BY attendance.attendance_date DESC'
);
$statement->execute(['student_id' => $studentId]);
$records = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Student Portal</p>
                <h1>My Attendance</h1>
                <p>View your attendance records by date.</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-light">Dashboard</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </header>

        <section class="content-panel">
            <h2>Attendance Records</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Marked By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$records): ?>
                            <tr>
                                <td colspan="6">No attendance records found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($records as $index => $record): ?>
                            <tr>
                                <td><?= e((string) ($index + 1)); ?></td>
                                <td><?= e($record['attendance_date']); ?></td>
                                <td><?= e($record['class_name']); ?></td>
                                <td>
                                    <span class="status-pill status-<?= e($record['status']); ?>">
                                        <?= e(ucfirst($record['status'])); ?>
                                    </span>
                                </td>
                                <td><?= e($record['remarks'] ?? ''); ?></td>
                                <td><?= e($record['marked_by_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>

