<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('student');

$database = Connection::connect();

$statement = $database->prepare(
    'SELECT students.*, users.full_name, users.email, users.phone, classes.class_name
     FROM students
     INNER JOIN users ON users.id = students.user_id
     INNER JOIN classes ON classes.id = students.class_id
     WHERE users.id = :user_id
     LIMIT 1'
);

$statement->execute(['user_id' => current_user()['id']]);
$student = $statement->fetch();

$attendanceCount = $database->prepare(
    'SELECT COUNT(*) FROM attendance WHERE student_id = :student_id'
);
$attendanceCount->execute(['student_id' => $student['id']]);
$totalAttendance = (int) $attendanceCount->fetchColumn();

$resultCount = $database->prepare(
    'SELECT COUNT(*) FROM results WHERE student_id = :student_id'
);
$resultCount->execute(['student_id' => $student['id']]);
$totalResults = (int) $resultCount->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Student Portal</p>
                <h1>Welcome, <?= e($student['full_name']); ?></h1>
                <p><?= e($student['class_name']); ?> · <?= e($student['admission_number']); ?></p>
            </div>
            <a href="../logout.php" class="btn btn-primary">Logout</a>
        </header>

        <section class="stats-grid">
            <article class="stat-card">
                <span>Class</span>
                <strong class="stat-text"><?= e($student['class_name']); ?></strong>
            </article>
            <article class="stat-card">
                <span>Attendance Records</span>
                <strong><?= e((string) $totalAttendance); ?></strong>
            </article>
            <article class="stat-card">
                <span>Result Entries</span>
                <strong><?= e((string) $totalResults); ?></strong>
            </article>
            <article class="stat-card">
                <span>Fee Status</span>
                <strong class="stat-text">Pending</strong>
            </article>
        </section>

        <section class="student-grid">
            <article class="content-panel">
                <h2>Profile</h2>
                <dl class="profile-list">
                    <div>
                        <dt>Full Name</dt>
                        <dd><?= e($student['full_name']); ?></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><?= e($student['email']); ?></dd>
                    </div>
                    <div>
                        <dt>Phone</dt>
                        <dd><?= e($student['phone'] ?? 'Not provided'); ?></dd>
                    </div>
                    <div>
                        <dt>Guardian</dt>
                        <dd><?= e($student['guardian_name'] ?? 'Not provided'); ?></dd>
                    </div>
                    <div>
                        <dt>Guardian Phone</dt>
                        <dd><?= e($student['guardian_phone'] ?? 'Not provided'); ?></dd>
                    </div>
                    <div>
                        <dt>Address</dt>
                        <dd><?= e($student['address'] ?? 'Not provided'); ?></dd>
                    </div>
                </dl>
            </article>

            <article class="content-panel">
                <h2>Academic Overview</h2>
                <p>
                    Attendance, results, and fees will appear here as the administrator
                    and teachers begin entering school records.
                </p>
            </article>
        </section>
    </main>
</body>
</html>
