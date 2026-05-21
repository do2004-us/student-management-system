<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$teachers = $database
    ->query('SELECT teachers.id, teachers.staff_number, users.full_name
             FROM teachers
             INNER JOIN users ON users.id = teachers.user_id
             ORDER BY users.full_name ASC')
    ->fetchAll();

$classes = $database->query('SELECT * FROM classes ORDER BY class_name ASC')->fetchAll();
$subjects = $database->query('SELECT * FROM subjects ORDER BY subject_name ASC')->fetchAll();

$assignments = $database
    ->query('SELECT teacher_subjects.id, users.full_name, teachers.staff_number,
                    classes.class_name, subjects.subject_name, subjects.subject_code
             FROM teacher_subjects
             INNER JOIN teachers ON teachers.id = teacher_subjects.teacher_id
             INNER JOIN users ON users.id = teachers.user_id
             INNER JOIN classes ON classes.id = teacher_subjects.class_id
             INNER JOIN subjects ON subjects.id = teacher_subjects.subject_id
             ORDER BY users.full_name ASC, classes.class_name ASC, subjects.subject_name ASC')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Subjects - <?= e(app_config('app_name')); ?></title>
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
                <a href="teacher-subjects.php" class="active">Teacher Subjects</a>
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
                    <h1>Teacher Subjects</h1>
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
                    <h2>Assign Teacher</h2>
                    <form action="save-teacher-subject.php" method="POST" class="stack-form">
                        <div class="form-group">
                            <label for="teacher_id">Teacher</label>
                            <select id="teacher_id" name="teacher_id" required>
                                <option value="">Select teacher</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= e((string) $teacher['id']); ?>">
                                        <?= e($teacher['full_name'] . ' (' . $teacher['staff_number'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="class_id">Class</label>
                            <select id="class_id" name="class_id" required>
                                <option value="">Select class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= e((string) $class['id']); ?>">
                                        <?= e($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject_id">Subject</label>
                            <select id="subject_id" name="subject_id" required>
                                <option value="">Select subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= e((string) $subject['id']); ?>">
                                        <?= e($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Assign Teacher</button>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Teacher Assignments</h2>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Teacher</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$assignments): ?>
                                    <tr>
                                        <td colspan="5">No teacher assignments found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($assignments as $index => $assignment): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($assignment['full_name']); ?></td>
                                        <td><?= e($assignment['class_name']); ?></td>
                                        <td><?= e($assignment['subject_name'] . ' (' . $assignment['subject_code'] . ')'); ?></td>
                                        <td class="table-actions">
                                            <form action="delete-teacher-subject.php" method="POST">
                                                <input type="hidden" name="id" value="<?= e((string) $assignment['id']); ?>">
                                                <button type="submit">Remove</button>
                                            </form>
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

