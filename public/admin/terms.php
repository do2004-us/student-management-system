<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$editTerm = null;

if (isset($_GET['edit'])) {
    $statement = $database->prepare('SELECT * FROM academic_terms WHERE id = :id LIMIT 1');
    $statement->execute(['id' => (int) $_GET['edit']]);
    $editTerm = $statement->fetch();
}

$terms = $database
    ->query('SELECT * FROM academic_terms ORDER BY academic_year DESC, id DESC')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Terms - <?= e(app_config('app_name')); ?></title>
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
                <a href="teacher-subjects.php">Teacher Subjects</a>
                <a href="terms.php" class="active">Terms</a>
                <a href="#">Attendance</a>
                <a href="#">Results</a>
                <a href="fees.php">Fees</a>
                <a href="reports.php">Reports</a>
            </nav>
        </aside>

        <section class="main-area">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Academic Terms</h1>
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
                    <h2><?= $editTerm ? 'Edit Term' : 'Add Term'; ?></h2>
                    <form action="save-term.php" method="POST" class="stack-form">
                        <input type="hidden" name="id" value="<?= e((string) ($editTerm['id'] ?? '')); ?>">

                        <div class="form-group">
                            <label for="term_name">Term Name</label>
                            <select id="term_name" name="term_name" required>
                                <option value="">Select term</option>
                                <?php foreach (['First Term', 'Second Term', 'Third Term'] as $termName): ?>
                                    <option value="<?= e($termName); ?>" <?= (($editTerm['term_name'] ?? '') === $termName) ? 'selected' : ''; ?>>
                                        <?= e($termName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="academic_year">Academic Year</label>
                            <input type="text" id="academic_year" name="academic_year" placeholder="2025/2026" value="<?= e($editTerm['academic_year'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?= e($editTerm['start_date'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?= e($editTerm['end_date'] ?? ''); ?>">
                        </div>

                        <label class="checkbox-row">
                            <input type="checkbox" name="is_active" value="1" <?= ((int) ($editTerm['is_active'] ?? 0) === 1) ? 'checked' : ''; ?>>
                            Make this the active term
                        </label>

                        <button type="submit" class="btn btn-primary">
                            <?= $editTerm ? 'Update Term' : 'Save Term'; ?>
                        </button>

                        <?php if ($editTerm): ?>
                            <a href="terms.php" class="text-link">Cancel edit</a>
                        <?php endif; ?>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Term List</h2>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Term</th>
                                    <th>Academic Year</th>
                                    <th>Dates</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$terms): ?>
                                    <tr>
                                        <td colspan="6">No academic terms found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($terms as $index => $term): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($term['term_name']); ?></td>
                                        <td><?= e($term['academic_year']); ?></td>
                                        <td><?= e(($term['start_date'] ?? '') . ' - ' . ($term['end_date'] ?? '')); ?></td>
                                        <td>
                                            <?php if ((int) $term['is_active'] === 1): ?>
                                                <span class="status-pill status-present">Active</span>
                                            <?php else: ?>
                                                <span class="status-pill status-excused">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="table-actions">
                                            <a href="terms.php?edit=<?= e((string) $term['id']); ?>">Edit</a>
                                            <form action="delete-term.php" method="POST">
                                                <input type="hidden" name="id" value="<?= e((string) $term['id']); ?>">
                                                <button type="submit">Delete</button>
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
