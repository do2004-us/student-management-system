<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();

$stats = [
    'students' => (int) $database->query('SELECT COUNT(*) FROM students')->fetchColumn(),
    'teachers' => (int) $database->query('SELECT COUNT(*) FROM teachers')->fetchColumn(),
    'classes' => (int) $database->query('SELECT COUNT(*) FROM classes')->fetchColumn(),
    'subjects' => (int) $database->query('SELECT COUNT(*) FROM subjects')->fetchColumn(),
    'attendance' => (int) $database->query('SELECT COUNT(*) FROM attendance')->fetchColumn(),
    'results' => (int) $database->query('SELECT COUNT(*) FROM results')->fetchColumn(),
];

$feeSummary = $database
    ->query('SELECT status, COUNT(*) AS total_records, COALESCE(SUM(amount_due), 0) AS total_due
             FROM fees
             GROUP BY status')
    ->fetchAll();

$attendanceSummary = $database
    ->query('SELECT status, COUNT(*) AS total_records
             FROM attendance
             GROUP BY status')
    ->fetchAll();

$classSummary = $database
    ->query('SELECT classes.class_name, COUNT(students.id) AS total_students
             FROM classes
             LEFT JOIN students ON students.class_id = classes.id
             GROUP BY classes.id
             ORDER BY classes.class_name ASC')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="app-layout">
        <?php require __DIR__ . '/../../app/Views/layouts/admin-sidebar.php'; ?>

        <section class="main-area">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Reports</h1>
                </div>
                <div class="topbar-user">
                    <span><?= e(current_user()['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-primary">Logout</a>
                </div>
            </header>

            <section class="stats-grid">
                <article class="stat-card">
                    <span>Students</span>
                    <strong><?= e((string) $stats['students']); ?></strong>
                </article>
                <article class="stat-card">
                    <span>Teachers</span>
                    <strong><?= e((string) $stats['teachers']); ?></strong>
                </article>
                <article class="stat-card">
                    <span>Attendance Records</span>
                    <strong><?= e((string) $stats['attendance']); ?></strong>
                </article>
                <article class="stat-card">
                    <span>Result Records</span>
                    <strong><?= e((string) $stats['results']); ?></strong>
                </article>
            </section>

            <section class="report-grid">
                <article class="content-panel">
                    <h2>Students By Class</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Total Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classSummary as $row): ?>
                                    <tr>
                                        <td><?= e($row['class_name']); ?></td>
                                        <td><?= e((string) $row['total_students']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="content-panel">
                    <h2>Attendance Summary</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Total Records</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$attendanceSummary): ?>
                                    <tr>
                                        <td colspan="2">No attendance records found.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($attendanceSummary as $row): ?>
                                    <tr>
                                        <td><?= e(ucfirst($row['status'])); ?></td>
                                        <td><?= e((string) $row['total_records']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="content-panel">
                    <h2>Fee Summary</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Records</th>
                                    <th>Total Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$feeSummary): ?>
                                    <tr>
                                        <td colspan="3">No fee records found.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($feeSummary as $row): ?>
                                    <tr>
                                        <td><?= e(ucfirst(str_replace('_', ' ', $row['status']))); ?></td>
                                        <td><?= e((string) $row['total_records']); ?></td>
                                        <td><?= e(number_format((float) $row['total_due'], 2)); ?></td>
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
