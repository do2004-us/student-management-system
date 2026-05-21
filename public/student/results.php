<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('student');

$database = Connection::connect();

$studentStatement = $database->prepare('SELECT id FROM students WHERE user_id = :user_id LIMIT 1');
$studentStatement->execute(['user_id' => current_user()['id']]);
$studentId = (int) $studentStatement->fetchColumn();

$statement = $database->prepare(
    'SELECT results.*, subjects.subject_name, subjects.subject_code,
            academic_terms.term_name, academic_terms.academic_year,
            users.full_name AS teacher_name
     FROM results
     INNER JOIN subjects ON subjects.id = results.subject_id
     INNER JOIN academic_terms ON academic_terms.id = results.term_id
     INNER JOIN teachers ON teachers.id = results.teacher_id
     INNER JOIN users ON users.id = teachers.user_id
     WHERE results.student_id = :student_id
     ORDER BY academic_terms.id DESC, subjects.subject_name ASC'
);
$statement->execute(['student_id' => $studentId]);
$results = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Student Portal</p>
                <h1>My Results</h1>
                <p>View your uploaded academic results.</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-light">Dashboard</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </header>

        <section class="content-panel">
            <h2>Result Records</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Term</th>
                            <th>Subject</th>
                            <th>Class Score</th>
                            <th>Exam Score</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Teacher</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$results): ?>
                            <tr>
                                <td colspan="8">No result records found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($results as $index => $result): ?>
                            <tr>
                                <td><?= e((string) ($index + 1)); ?></td>
                                <td><?= e($result['term_name'] . ' ' . $result['academic_year']); ?></td>
                                <td><?= e($result['subject_name'] . ' (' . $result['subject_code'] . ')'); ?></td>
                                <td><?= e((string) $result['class_score']); ?></td>
                                <td><?= e((string) $result['exam_score']); ?></td>
                                <td><?= e((string) $result['total_score']); ?></td>
                                <td><span class="status-pill status-excused"><?= e($result['grade']); ?></span></td>
                                <td><?= e($result['teacher_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>

