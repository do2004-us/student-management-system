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
];

$attendanceSummary = $database
    ->query('SELECT status, COUNT(*) AS total FROM attendance GROUP BY status')
    ->fetchAll();

$feeSummary = $database
    ->query('SELECT status, COUNT(*) AS total FROM fees GROUP BY status')
    ->fetchAll();

$performanceSummary = $database
    ->query('SELECT subjects.subject_name, ROUND(AVG(results.total_score), 2) AS average_score
             FROM results
             INNER JOIN subjects ON subjects.id = results.subject_id
             GROUP BY subjects.id
             ORDER BY subjects.subject_name ASC
             LIMIT 8')
    ->fetchAll();

$recentActivities = $database
    ->query('(SELECT "Student registered" AS activity, users.full_name AS details, students.created_at AS created_at
              FROM students INNER JOIN users ON users.id = students.user_id)
             UNION ALL
             (SELECT "Teacher registered" AS activity, users.full_name AS details, teachers.created_at AS created_at
              FROM teachers INNER JOIN users ON users.id = teachers.user_id)
             UNION ALL
             (SELECT "Payment recorded" AS activity, CONCAT("GHS ", amount_paid) AS details, created_at
              FROM payments)
             ORDER BY created_at DESC
             LIMIT 6')
    ->fetchAll();

$attendanceLabels = array_column($attendanceSummary, 'status') ?: ['No data'];
$attendanceValues = array_map('intval', array_column($attendanceSummary, 'total')) ?: [1];
$feeLabels = array_map(fn ($row) => ucfirst(str_replace('_', ' ', $row['status'])), $feeSummary) ?: ['No data'];
$feeValues = array_map('intval', array_column($feeSummary, 'total')) ?: [0];
$performanceLabels = array_column($performanceSummary, 'subject_name') ?: ['No results'];
$performanceValues = array_map('floatval', array_column($performanceSummary, 'average_score')) ?: [0];

$attendanceTotal = array_sum($attendanceValues);
$presentCount = 0;
foreach ($attendanceSummary as $row) {
    if ($row['status'] === 'present') {
        $presentCount = (int) $row['total'];
    }
}
$attendanceRate = $attendanceTotal > 0 ? round(($presentCount / $attendanceTotal) * 100) : 0;

$averagePerformance = $performanceValues ? round(array_sum($performanceValues) / max(1, count($performanceValues))) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/premium-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <main class="app-layout">
        <?php require __DIR__ . '/../../app/Views/layouts/admin-sidebar.php'; ?>

        <section class="main-area">
            <header class="topbar">
                <button type="button" class="icon-btn" data-mobile-nav aria-label="Open navigation">☰</button>
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Analytics Dashboard</h1>
                </div>
                <div class="topbar-actions">
                    <button type="button" class="icon-btn" data-theme-toggle aria-label="Toggle dark mode">◐</button>

                    <div class="dropdown-wrap" data-notification-wrap>
                        <button type="button" class="icon-btn" data-notification-toggle aria-label="Open notifications">
                            🔔
                            <span class="notification-badge"><?= e((string) $database->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0')->fetchColumn()); ?></span>
                        </button>
                        <div class="dropdown-menu" data-notification-menu>
                            <div><strong>Notifications</strong></div>
                            <a href="notifications.php">Open notification center</a>
                        </div>
                    </div>

                    <div class="dropdown-wrap" data-profile-wrap>
                        <button type="button" class="btn btn-light" data-profile-toggle>
                            <?= e(current_user()['full_name']); ?>
                        </button>
                        <div class="dropdown-menu" data-profile-menu>
                            <div><strong><?= e(current_user()['email']); ?></strong></div>
                            <a href="../change-password.php">Change password</a>
                            <a href="../logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <article class="stat-card">
                    <span>Total Students</span>
                    <strong><?= e((string) $stats['students']); ?></strong>
                </article>
                <article class="stat-card">
                    <span>Total Teachers</span>
                    <strong><?= e((string) $stats['teachers']); ?></strong>
                </article>
                <article class="stat-card">
                    <span>Total Classes</span>
                    <strong><?= e((string) $stats['classes']); ?></strong>
                </article>
                <article class="stat-card">
                    <span>Total Subjects</span>
                    <strong><?= e((string) $stats['subjects']); ?></strong>
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
                    <h2>Smart Insights</h2>
                    <div class="insight-list">
                        <div class="insight-item">
                            <strong>Attendance health</strong>
                            <span><?= e((string) $attendanceRate); ?>% present rate across recorded attendance.</span>
                            <div class="progress-track">
                                <div class="progress-bar" data-progress="<?= e((string) $attendanceRate); ?>"></div>
                            </div>
                        </div>
                        <div class="insight-item">
                            <strong>Performance prediction</strong>
                            <span>
                                <?= $averagePerformance >= 70
                                    ? 'Students are trending strongly based on uploaded scores.'
                                    : 'Some learners may need intervention based on current averages.'; ?>
                            </span>
                            <div class="progress-track">
                                <div class="progress-bar" data-progress="<?= e((string) $averagePerformance); ?>"></div>
                            </div>
                        </div>
                        <div class="insight-item">
                            <strong>Recommendation</strong>
                            <span>Review absent students weekly and follow up on unpaid fees before term end.</span>
                        </div>
                    </div>
                </article>
            </section>

            <section class="analytics-grid">
                <article class="content-panel chart-card">
                    <h2>Attendance Analytics</h2>
                    <canvas
                        id="attendanceChart"
                        data-labels='<?= e(json_encode($attendanceLabels)); ?>'
                        data-values='<?= e(json_encode($attendanceValues)); ?>'
                    ></canvas>
                </article>

                <article class="content-panel chart-card">
                    <h2>Fee Analytics</h2>
                    <canvas
                        id="feeChart"
                        data-labels='<?= e(json_encode($feeLabels)); ?>'
                        data-values='<?= e(json_encode($feeValues)); ?>'
                    ></canvas>
                </article>
            </section>

            <section class="dashboard-grid">
                <article class="content-panel">
                    <h2>Recent Activities</h2>
                    <div class="mini-list">
                        <?php if (!$recentActivities): ?>
                            <p>No recent activities yet.</p>
                        <?php endif; ?>

                        <?php foreach ($recentActivities as $activity): ?>
                            <div>
                                <strong><?= e($activity['activity']); ?></strong>
                                <span><?= e($activity['details']); ?></span>
                                <small><?= e($activity['created_at']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="content-panel">
                    <h2>Quick Actions</h2>
                    <div class="insight-list">
                        <a class="btn btn-primary" href="students.php">Add Student</a>
                        <a class="btn btn-light" href="teachers.php">Add Teacher</a>
                        <a class="btn btn-light" href="attendance.php">Review Attendance</a>
                        <a class="btn btn-light" href="reports.php">Open Reports</a>
                    </div>
                </article>
            </section>
        </section>
    </main>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
