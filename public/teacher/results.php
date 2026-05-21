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

$term = $database
    ->query('SELECT * FROM academic_terms WHERE is_active = 1 LIMIT 1')
    ->fetch();

$assignmentStatement = $database->prepare(
    'SELECT teacher_subjects.id, classes.id AS class_id, classes.class_name,
            subjects.id AS subject_id, subjects.subject_name, subjects.subject_code
     FROM teacher_subjects
     INNER JOIN classes ON classes.id = teacher_subjects.class_id
     INNER JOIN subjects ON subjects.id = teacher_subjects.subject_id
     WHERE teacher_subjects.teacher_id = :teacher_id
     ORDER BY classes.class_name ASC, subjects.subject_name ASC'
);
$assignmentStatement->execute(['teacher_id' => $teacherId]);
$assignments = $assignmentStatement->fetchAll();

$assignmentId = (int) ($_GET['assignment_id'] ?? 0);
$selectedAssignment = null;
$students = [];

if ($assignmentId > 0) {
    foreach ($assignments as $assignment) {
        if ((int) $assignment['id'] === $assignmentId) {
            $selectedAssignment = $assignment;
            break;
        }
    }

    if ($selectedAssignment) {
        $studentStatement = $database->prepare(
            'SELECT students.id, students.admission_number, users.full_name
             FROM students
             INNER JOIN users ON users.id = students.user_id
             WHERE students.class_id = :class_id
             ORDER BY users.full_name ASC'
        );
        $studentStatement->execute(['class_id' => $selectedAssignment['class_id']]);
        $students = $studentStatement->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Results - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Teacher Portal</p>
                <h1>Upload Results</h1>
                <p>
                    Active term:
                    <?= $term ? e($term['term_name'] . ' ' . $term['academic_year']) : 'No active term set'; ?>
                </p>
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
            <form method="GET" action="results.php" class="filter-form">
                <div class="form-group">
                    <label for="assignment_id">Class and Subject</label>
                    <select id="assignment_id" name="assignment_id" required>
                        <option value="">Select assignment</option>
                        <?php foreach ($assignments as $assignment): ?>
                            <option value="<?= e((string) $assignment['id']); ?>" <?= $assignmentId === (int) $assignment['id'] ? 'selected' : ''; ?>>
                                <?= e($assignment['class_name'] . ' - ' . $assignment['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Load Students</button>
            </form>
        </section>

        <?php if ($selectedAssignment && $term): ?>
            <section class="content-panel">
                <h2><?= e($selectedAssignment['class_name'] . ' - ' . $selectedAssignment['subject_name']); ?></h2>

                <form action="save-results.php" method="POST" class="stack-form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="assignment_id" value="<?= e((string) $selectedAssignment['id']); ?>">
                    <input type="hidden" name="class_id" value="<?= e((string) $selectedAssignment['class_id']); ?>">
                    <input type="hidden" name="subject_id" value="<?= e((string) $selectedAssignment['subject_id']); ?>">
                    <input type="hidden" name="term_id" value="<?= e((string) $term['id']); ?>">

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Admission No.</th>
                                    <th>Class Score</th>
                                    <th>Exam Score</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$students): ?>
                                    <tr>
                                        <td colspan="6">No students found in this class.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($student['full_name']); ?></td>
                                        <td><?= e($student['admission_number']); ?></td>
                                        <td>
                                            <input type="number" name="results[<?= e((string) $student['id']); ?>][class_score]" min="0" max="50" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="number" name="results[<?= e((string) $student['id']); ?>][exam_score]" min="0" max="50" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="text" name="results[<?= e((string) $student['id']); ?>][remark]" placeholder="Optional">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($students): ?>
                        <button type="submit" class="btn btn-primary">Save Results</button>
                    <?php endif; ?>
                </form>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
