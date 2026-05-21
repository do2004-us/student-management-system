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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= e(app_config('app_name')); ?></title>
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
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="students.php">Students</a>
                <a href="teachers.php">Teachers</a>
                <a href="classes.php">Classes</a>
                <a href="subjects.php">Subjects</a>
                <a href="class-subjects.php">Class Subjects</a>
                <a href="teacher-subjects.php">Teacher Subjects</a>
                <a href="#">Attendance</a>
                <a href="#">Results</a>
                <a href="#">Fees</a>
                <a href="#">Reports</a>
            </nav>
        </aside>

        <section class="main-area">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Dashboard</h1>
                </div>
                <div class="topbar-user">
                    <span><?= e(current_user()['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-primary">Logout</a>
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

            <section class="content-panel">
                <div>
                    <h2>Next Modules</h2>
                    <p>
                        We will now build student management, teacher management, classes,
                        subjects, attendance, results, fees, and reports.
                    </p>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
