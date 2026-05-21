<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();

$classId = (int) ($_GET['class_id'] ?? 0);
$subjectId = (int) ($_GET['subject_id'] ?? 0);
$termId = (int) ($_GET['term_id'] ?? 0);

$classes = $database->query('SELECT * FROM classes ORDER BY class_name ASC')->fetchAll();
$subjects = $database->query('SELECT * FROM subjects ORDER BY subject_name ASC')->fetchAll();
$terms = $database->query('SELECT * FROM academic_terms ORDER BY id DESC')->fetchAll();

$where = [];
$params = [];

if ($classId > 0) {
    $where[] = 'results.class_id = :class_id';
    $params['class_id'] = $classId;
}

if ($subjectId > 0) {
    $where[] = 'results.subject_id = :subject_id';
    $params['subject_id'] = $subjectId;
}

if ($termId > 0) {
    $where[] = 'results.term_id = :term_id';
    $params['term_id'] = $termId;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$statement = $database->prepare(
    "SELECT results.*, student_user.full_name AS student_name,
            teacher_user.full_name AS teacher_name, students.admission_number,
            classes.class_name, subjects.subject_name, subjects.subject_code,
            academic_terms.term_name, academic_terms.academic_year
     FROM results
     INNER JOIN students ON students.id = results.student_id
     INNER JOIN users AS student_user ON student_user.id = students.user_id
     INNER JOIN teachers ON teachers.id = results.teacher_id
     INNER JOIN users AS teacher_user ON teacher_user.id = teachers.user_id
     INNER JOIN classes ON classes.id = results.class_id
     INNER JOIN subjects ON subjects.id = results.subject_id
     INNER JOIN academic_terms ON academic_terms.id = results.term_id
     $whereSql
     ORDER BY academic_terms.id DESC, classes.class_name ASC, student_user.full_name ASC"
);
$statement->execute($params);
$records = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Review - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="app-layout">
        <?php require __DIR__ . '/../../app/Views/layouts/admin-sidebar.php'; ?>

        <section class="main-area">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Results Review</h1>
                </div>
                <div class="topbar-user">
                    <span><?= e(current_user()['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-primary">Logout</a>
                </div>
            </header>

            <section class="content-panel">
                <form method="GET" action="results.php" class="filter-form filter-form-wide">
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
                        <label for="subject_id">Subject</label>
                        <select id="subject_id" name="subject_id">
                            <option value="">All subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= e((string) $subject['id']); ?>" <?= $subjectId === (int) $subject['id'] ? 'selected' : ''; ?>>
                                    <?= e($subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="term_id">Term</label>
                        <select id="term_id" name="term_id">
                            <option value="">All terms</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= e((string) $term['id']); ?>" <?= $termId === (int) $term['id'] ? 'selected' : ''; ?>>
                                    <?= e($term['term_name'] . ' ' . $term['academic_year']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </section>

            <section class="content-panel">
                <h2>Result Records</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Term</th>
                                <th>Total</th>
                                <th>Grade</th>
                                <th>Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$records): ?>
                                <tr>
                                    <td colspan="8">No result records found.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($records as $index => $record): ?>
                                <tr>
                                    <td><?= e((string) ($index + 1)); ?></td>
                                    <td><?= e($record['student_name']); ?></td>
                                    <td><?= e($record['class_name']); ?></td>
                                    <td><?= e($record['subject_name'] . ' (' . $record['subject_code'] . ')'); ?></td>
                                    <td><?= e($record['term_name'] . ' ' . $record['academic_year']); ?></td>
                                    <td><?= e((string) $record['total_score']); ?></td>
                                    <td><span class="status-pill status-excused"><?= e($record['grade']); ?></span></td>
                                    <td><?= e($record['teacher_name']); ?></td>
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

