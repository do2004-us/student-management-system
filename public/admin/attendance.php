<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();

$classId = (int) ($_GET['class_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$classes = $database->query('SELECT * FROM classes ORDER BY class_name ASC')->fetchAll();

$where = [];
$params = [];

if ($classId > 0) {
    $where[] = 'attendance.class_id = :class_id';
    $params['class_id'] = $classId;
}

if (in_array($status, ['present', 'absent', 'late', 'excused'], true)) {
    $where[] = 'attendance.status = :status';
    $params['status'] = $status;
}

if ($dateFrom !== '') {
    $where[] = 'attendance.attendance_date >= :date_from';
    $params['date_from'] = $dateFrom;
}

if ($dateTo !== '') {
    $where[] = 'attendance.attendance_date <= :date_to';
    $params['date_to'] = $dateTo;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$statement = $database->prepare(
    "SELECT attendance.*, student_user.full_name AS student_name,
            marker.full_name AS marked_by_name, classes.class_name,
            students.admission_number
     FROM attendance
     INNER JOIN students ON students.id = attendance.student_id
     INNER JOIN users AS student_user ON student_user.id = students.user_id
     INNER JOIN users AS marker ON marker.id = attendance.marked_by
     INNER JOIN classes ON classes.id = attendance.class_id
     $whereSql
     ORDER BY attendance.attendance_date DESC, classes.class_name ASC, student_user.full_name ASC"
);
$statement->execute($params);
$records = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Review - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="app-layout">
        <?php require __DIR__ . '/../../app/Views/layouts/admin-sidebar.php'; ?>

        <section class="main-area">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Attendance Review</h1>
                </div>
                <div class="topbar-user">
                    <span><?= e(current_user()['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-primary">Logout</a>
                </div>
            </header>

            <section class="content-panel">
                <form method="GET" action="attendance.php" class="filter-form filter-form-wide">
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id">
                            <option value="">All classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= e((string) $class['id']); ?>" <?= $classId === (int) $class['id'] ? 'selected' : ''; ?>>
                                    <?= e($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All statuses</option>
                            <?php foreach (['present', 'absent', 'late', 'excused'] as $option): ?>
                                <option value="<?= e($option); ?>" <?= $status === $option ? 'selected' : ''; ?>>
                                    <?= e(ucfirst($option)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_from">From</label>
                        <input type="date" id="date_from" name="date_from" value="<?= e($dateFrom); ?>">
                    </div>

                    <div class="form-group">
                        <label for="date_to">To</label>
                        <input type="date" id="date_to" name="date_to" value="<?= e($dateTo); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </section>

            <section class="content-panel">
                <h2>Attendance Records</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Admission No.</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Marked By</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$records): ?>
                                <tr>
                                    <td colspan="8">No attendance records found.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($records as $index => $record): ?>
                                <tr>
                                    <td><?= e((string) ($index + 1)); ?></td>
                                    <td><?= e($record['attendance_date']); ?></td>
                                    <td><?= e($record['student_name']); ?></td>
                                    <td><?= e($record['admission_number']); ?></td>
                                    <td><?= e($record['class_name']); ?></td>
                                    <td>
                                        <span class="status-pill status-<?= e($record['status']); ?>">
                                            <?= e(ucfirst($record['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?= e($record['marked_by_name']); ?></td>
                                    <td><?= e($record['remarks'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</body>
</html>

