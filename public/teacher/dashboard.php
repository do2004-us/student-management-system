<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('teacher');

$database = Connection::connect();

$statement = $database->prepare(
    'SELECT teachers.*, users.full_name, users.email, users.phone
     FROM teachers
     INNER JOIN users ON users.id = teachers.user_id
     WHERE users.id = :user_id
     LIMIT 1'
);

$statement->execute(['user_id' => current_user()['id']]);
$teacher = $statement->fetch();

$assignmentStatement = $database->prepare(
    'SELECT classes.class_name, subjects.subject_name, subjects.subject_code
     FROM teacher_subjects
     INNER JOIN classes ON classes.id = teacher_subjects.class_id
     INNER JOIN subjects ON subjects.id = teacher_subjects.subject_id
     WHERE teacher_subjects.teacher_id = :teacher_id
     ORDER BY classes.class_name ASC, subjects.subject_name ASC'
);
$assignmentStatement->execute(['teacher_id' => $teacher['id']]);
$assignments = $assignmentStatement->fetchAll();

$assignedClasses = array_unique(array_column($assignments, 'class_name'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Teacher Portal</p>
                <h1>Welcome, <?= e($teacher['full_name']); ?></h1>
                <p><?= e($teacher['staff_number']); ?> · <?= e($teacher['qualification'] ?? 'Teacher'); ?></p>
            </div>
            <div class="header-actions">
                <a href="attendance.php" class="btn btn-light">Attendance</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </header>

        <section class="stats-grid">
            <article class="stat-card">
                <span>Assigned Classes</span>
                <strong><?= e((string) count($assignedClasses)); ?></strong>
            </article>
            <article class="stat-card">
                <span>Subject Assignments</span>
                <strong><?= e((string) count($assignments)); ?></strong>
            </article>
            <article class="stat-card">
                <span>Staff Number</span>
                <strong class="stat-text"><?= e($teacher['staff_number']); ?></strong>
            </article>
            <article class="stat-card">
                <span>Status</span>
                <strong class="stat-text">Active</strong>
            </article>
        </section>

        <section class="student-grid">
            <article class="content-panel">
                <h2>Profile</h2>
                <dl class="profile-list">
                    <div>
                        <dt>Full Name</dt>
                        <dd><?= e($teacher['full_name']); ?></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><?= e($teacher['email']); ?></dd>
                    </div>
                    <div>
                        <dt>Phone</dt>
                        <dd><?= e($teacher['phone'] ?? 'Not provided'); ?></dd>
                    </div>
                    <div>
                        <dt>Qualification</dt>
                        <dd><?= e($teacher['qualification'] ?? 'Not provided'); ?></dd>
                    </div>
                    <div>
                        <dt>Employment Date</dt>
                        <dd><?= e($teacher['employment_date'] ?? 'Not provided'); ?></dd>
                    </div>
                    <div>
                        <dt>Address</dt>
                        <dd><?= e($teacher['address'] ?? 'Not provided'); ?></dd>
                    </div>
                </dl>
            </article>

            <article class="content-panel">
                <h2>Teaching Assignments</h2>

                <?php if (!$assignments): ?>
                    <p>No class or subject has been assigned to you yet.</p>
                <?php else: ?>
                    <div class="mini-list">
                        <?php foreach ($assignments as $assignment): ?>
                            <div>
                                <strong><?= e($assignment['class_name']); ?></strong>
                                <span><?= e($assignment['subject_name'] . ' (' . $assignment['subject_code'] . ')'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>
    </main>
</body>
</html>
