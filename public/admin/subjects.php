<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$editSubject = null;

if (isset($_GET['edit'])) {
    $statement = $database->prepare('SELECT * FROM subjects WHERE id = :id LIMIT 1');
    $statement->execute(['id' => (int) $_GET['edit']]);
    $editSubject = $statement->fetch();
}

$subjects = $database
    ->query('SELECT * FROM subjects ORDER BY subject_name ASC')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - <?= e(app_config('app_name')); ?></title>
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
                <a href="subjects.php" class="active">Subjects</a>
                <a href="class-subjects.php">Class Subjects</a>
                <a href="teacher-subjects.php">Teacher Subjects</a>
                <a href="terms.php">Terms</a>
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
                    <h1>Manage Subjects</h1>
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
                    <h2><?= $editSubject ? 'Edit Subject' : 'Add Subject'; ?></h2>
                    <form action="save-subject.php" method="POST" class="stack-form">
                        <input type="hidden" name="id" value="<?= e((string) ($editSubject['id'] ?? '')); ?>">

                        <div class="form-group">
                            <label for="subject_name">Subject Name</label>
                            <input
                                type="text"
                                id="subject_name"
                                name="subject_name"
                                placeholder="Example: Mathematics"
                                value="<?= e($editSubject['subject_name'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="subject_code">Subject Code</label>
                            <input
                                type="text"
                                id="subject_code"
                                name="subject_code"
                                placeholder="Example: MATH"
                                value="<?= e($editSubject['subject_code'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <?= $editSubject ? 'Update Subject' : 'Save Subject'; ?>
                        </button>

                        <?php if ($editSubject): ?>
                            <a href="subjects.php" class="text-link">Cancel edit</a>
                        <?php endif; ?>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Subject List</h2>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject Name</th>
                                    <th>Subject Code</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$subjects): ?>
                                    <tr>
                                        <td colspan="4">No subjects found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($subjects as $index => $subject): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($subject['subject_name']); ?></td>
                                        <td><?= e($subject['subject_code']); ?></td>
                                        <td class="table-actions">
                                            <a href="subjects.php?edit=<?= e((string) $subject['id']); ?>">Edit</a>
                                            <form action="delete-subject.php" method="POST">
                                                <input type="hidden" name="id" value="<?= e((string) $subject['id']); ?>">
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
