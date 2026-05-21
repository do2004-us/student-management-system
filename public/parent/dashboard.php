<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('parent');

$database = Connection::connect();

$parentStatement = $database->prepare(
    'SELECT parents.*, users.full_name, users.email, users.phone
     FROM parents
     INNER JOIN users ON users.id = parents.user_id
     WHERE users.id = :user_id
     LIMIT 1'
);
$parentStatement->execute(['user_id' => current_user()['id']]);
$parent = $parentStatement->fetch();

$childrenStatement = $database->prepare(
    'SELECT students.id, students.admission_number, student_user.full_name, classes.class_name,
            parent_students.relationship
     FROM parent_students
     INNER JOIN students ON students.id = parent_students.student_id
     INNER JOIN users AS student_user ON student_user.id = students.user_id
     INNER JOIN classes ON classes.id = students.class_id
     WHERE parent_students.parent_id = :parent_id
     ORDER BY student_user.full_name ASC'
);
$childrenStatement->execute(['parent_id' => $parent['id']]);
$children = $childrenStatement->fetchAll();
$selectedStudentId = (int) ($_GET['student_id'] ?? ($children[0]['id'] ?? 0));

$selectedChild = null;
foreach ($children as $child) {
    if ((int) $child['id'] === $selectedStudentId) {
        $selectedChild = $child;
        break;
    }
}

$attendanceRows = [];
$results = [];
$fees = [];
$attendanceRate = 0;
$averageScore = 0;

if ($selectedChild) {
    $attendanceStatement = $database->prepare(
        'SELECT status, COUNT(*) AS total
         FROM attendance
         WHERE student_id = :student_id
         GROUP BY status'
    );
    $attendanceStatement->execute(['student_id' => $selectedStudentId]);
    $attendanceRows = $attendanceStatement->fetchAll();

    $present = 0;
    $attendanceTotal = 0;
    foreach ($attendanceRows as $row) {
        $attendanceTotal += (int) $row['total'];
        if ($row['status'] === 'present') {
            $present = (int) $row['total'];
        }
    }
    $attendanceRate = $attendanceTotal > 0 ? round(($present / $attendanceTotal) * 100) : 0;

    $resultStatement = $database->prepare(
        'SELECT results.*, subjects.subject_name, subjects.subject_code,
                academic_terms.term_name, academic_terms.academic_year
         FROM results
         INNER JOIN subjects ON subjects.id = results.subject_id
         INNER JOIN academic_terms ON academic_terms.id = results.term_id
         WHERE results.student_id = :student_id
         ORDER BY academic_terms.id DESC, subjects.subject_name ASC'
    );
    $resultStatement->execute(['student_id' => $selectedStudentId]);
    $results = $resultStatement->fetchAll();

    if ($results) {
        $averageScore = round(array_sum(array_map('floatval', array_column($results, 'total_score'))) / count($results));
    }

    $feeStatement = $database->prepare(
        'SELECT fees.*, academic_terms.term_name, academic_terms.academic_year,
                COALESCE(SUM(payments.amount_paid), 0) AS paid_amount
         FROM fees
         INNER JOIN academic_terms ON academic_terms.id = fees.term_id
         LEFT JOIN payments ON payments.fee_id = fees.id
         WHERE fees.student_id = :student_id
         GROUP BY fees.id
         ORDER BY fees.created_at DESC'
    );
    $feeStatement->execute(['student_id' => $selectedStudentId]);
    $fees = $feeStatement->fetchAll();
}

$attendanceLabels = array_column($attendanceRows, 'status') ?: ['No data'];
$attendanceValues = array_map('intval', array_column($attendanceRows, 'total')) ?: [1];
$performanceLabels = array_column($results, 'subject_name') ?: ['No results'];
$performanceValues = array_map('floatval', array_column($results, 'total_score')) ?: [0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/premium-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Parent Portal</p>
                <h1>Welcome, <?= e($parent['full_name']); ?></h1>
                <p>Monitor attendance, results, fees, and school messages.</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-light" data-theme-toggle>Theme</button>
                <a href="../notifications.php" class="btn btn-light">Notifications</a>
                <a href="../change-password.php" class="btn btn-light">Password</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </header>

        <?php if (!$children): ?>
            <section class="content-panel">
                <h2>No linked student</h2>
                <p>Please contact the school administrator to link your account to a student.</p>
            </section>
        <?php else: ?>
            <section class="content-panel">
                <form method="GET" action="dashboard.php" class="filter-form">
                    <div class="form-group">
                        <label for="student_id">Select Child</label>
                        <select id="student_id" name="student_id">
                            <?php foreach ($children as $child): ?>
                                <option value="<?= e((string) $child['id']); ?>" <?= $selectedStudentId === (int) $child['id'] ? 'selected' : ''; ?>>
                                    <?= e($child['full_name'] . ' - ' . $child['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">View</button>
                </form>
            </section>

            <section class="stats-grid">
                <article class="stat-card">
                    <span>Student</span>
                    <strong class="stat-text"><?= e($selectedChild['full_name']); ?></strong>
                </article>
                <article class="stat-card">
                    <span>Class</span>
                    <strong class="stat-text"><?= e($selectedChild['class_name']); ?></strong>
                </article>
                <article class="stat-card">
                    <span>Attendance</span>
                    <strong><?= e((string) $attendanceRate); ?>%</strong>
                </article>
                <article class="stat-card">
                    <span>Average Score</span>
                    <strong><?= e((string) $averageScore); ?>%</strong>
                </article>
            </section>

            <section class="dashboard-grid">
                <article class="content-panel chart-card">
                    <h2>Performance Analytics</h2>
                    <canvas
                        id="performanceChart"
                        data-labels='<?= e(json_encode($performanceLabels)); ?>'
                        data-values='<?= e(json_encode($performanceValues)); ?>'
                    ></canvas>
                </article>

                <article class="content-panel">
                    <h2>Smart Parent Insights</h2>
                    <div class="insight-list">
                        <div class="insight-item">
                            <strong>Attendance risk</strong>
                            <span>
                                <?= $attendanceRate >= 80
                                    ? 'Attendance is healthy. Keep encouraging consistency.'
                                    : 'Attendance needs attention. Please follow up with the school.'; ?>
                            </span>
                            <div class="progress-track">
                                <div class="progress-bar" data-progress="<?= e((string) $attendanceRate); ?>"></div>
                            </div>
                        </div>
                        <div class="insight-item">
                            <strong>Academic recommendation</strong>
                            <span>
                                <?= $averageScore >= 70
                                    ? 'Performance is strong based on available results.'
                                    : 'Extra revision and teacher consultation may help improve performance.'; ?>
                            </span>
                            <div class="progress-track">
                                <div class="progress-bar" data-progress="<?= e((string) $averageScore); ?>"></div>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <section class="analytics-grid">
                <article class="content-panel chart-card">
                    <h2>Attendance Breakdown</h2>
                    <canvas
                        id="attendanceChart"
                        data-labels='<?= e(json_encode($attendanceLabels)); ?>'
                        data-values='<?= e(json_encode($attendanceValues)); ?>'
                    ></canvas>
                </article>

                <article class="content-panel">
                    <h2>Fee Status</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Term</th>
                                    <th>Due</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$fees): ?>
                                    <tr>
                                        <td colspan="4">No fee records found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($fees as $fee): ?>
                                    <tr>
                                        <td><?= e($fee['term_name'] . ' ' . $fee['academic_year']); ?></td>
                                        <td>GHS <?= e(number_format((float) $fee['amount_due'], 2)); ?></td>
                                        <td>GHS <?= e(number_format((float) $fee['paid_amount'], 2)); ?></td>
                                        <td><?= e(ucfirst(str_replace('_', ' ', $fee['status']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        <?php endif; ?>
    </main>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>

