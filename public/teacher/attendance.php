<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('teacher');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$teacherStatement = $database->prepare('SELECT id FROM teachers WHERE user_id = :user_id LIMIT 1');
$teacherStatement->execute(['user_id' => current_user()['id']]);
$teacherId = (int) $teacherStatement->fetchColumn();

$classStatement = $database->prepare(
    'SELECT DISTINCT classes.id, classes.class_name
     FROM teacher_subjects
     INNER JOIN classes ON classes.id = teacher_subjects.class_id
     WHERE teacher_subjects.teacher_id = :teacher_id
     ORDER BY classes.class_name ASC'
);
$classStatement->execute(['teacher_id' => $teacherId]);
$assignedClasses = $classStatement->fetchAll();

$selectedClassId = (int) ($_GET['class_id'] ?? 0);
$attendanceDate = $_GET['attendance_date'] ?? date('Y-m-d');
$students = [];

if ($selectedClassId > 0) {
    $studentStatement = $database->prepare(
        'SELECT students.id, students.admission_number, users.full_name
         FROM students
         INNER JOIN users ON users.id = students.user_id
         WHERE students.class_id = :class_id
         ORDER BY users.full_name ASC'
    );
    $studentStatement->execute(['class_id' => $selectedClassId]);
    $students = $studentStatement->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Teacher Portal</p>
                <h1>Mark Attendance</h1>
                <p>Select a class and mark attendance for the chosen date.</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-light">Dashboard</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error); ?></div>
        <?php endif; ?>

        <section class="content-panel">
            <form method="GET" action="attendance.php" class="filter-form">
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" required>
                        <option value="">Select class</option>
                        <?php foreach ($assignedClasses as $class): ?>
                            <option value="<?= e((string) $class['id']); ?>" <?= $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                <?= e($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="attendance_date">Date</label>
                    <input type="date" id="attendance_date" name="attendance_date" value="<?= e($attendanceDate); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Load Students</button>
            </form>
        </section>

        <?php if ($selectedClassId > 0): ?>
            <section class="content-panel">
                <h2>Student Attendance</h2>

                <form action="save-attendance.php" method="POST" class="stack-form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="class_id" value="<?= e((string) $selectedClassId); ?>">
                    <input type="hidden" name="attendance_date" value="<?= e($attendanceDate); ?>">

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Admission No.</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$students): ?>
                                    <tr>
                                        <td colspan="5">No students found in this class.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($student['full_name']); ?></td>
                                        <td><?= e($student['admission_number']); ?></td>
                                        <td>
                                            <select name="attendance[<?= e((string) $student['id']); ?>][status]" required>
                                                <option value="present">Present</option>
                                                <option value="absent">Absent</option>
                                                <option value="late">Late</option>
                                                <option value="excused">Excused</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance[<?= e((string) $student['id']); ?>][remarks]" placeholder="Optional">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($students): ?>
                        <button type="submit" class="btn btn-primary">Save Attendance</button>
                    <?php endif; ?>
                </form>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
