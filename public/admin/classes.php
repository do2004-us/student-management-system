<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$editClass = null;

if (isset($_GET['edit'])) {
    $statement = $database->prepare('SELECT * FROM classes WHERE id = :id LIMIT 1');
    $statement->execute(['id' => (int) $_GET['edit']]);
    $editClass = $statement->fetch();
}

$classes = $database
    ->query('SELECT classes.*, users.full_name AS class_teacher_name
             FROM classes
             LEFT JOIN teachers ON teachers.id = classes.class_teacher_id
             LEFT JOIN users ON users.id = teachers.user_id
             ORDER BY classes.class_name ASC')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes - <?= e(app_config('app_name')); ?></title>
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
                <a href="classes.php" class="active">Classes</a>
                <a href="subjects.php">Subjects</a>
                <a href="class-subjects.php">Class Subjects</a>
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
                    <h1>Manage Classes</h1>
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
                    <h2><?= $editClass ? 'Edit Class' : 'Add Class'; ?></h2>
                    <form action="save-class.php" method="POST" class="stack-form">
                        <input type="hidden" name="id" value="<?= e((string) ($editClass['id'] ?? '')); ?>">

                        <div class="form-group">
                            <label for="class_name">Class Name</label>
                            <input
                                type="text"
                                id="class_name"
                                name="class_name"
                                placeholder="Example: Grade 1"
                                value="<?= e($editClass['class_name'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <?= $editClass ? 'Update Class' : 'Save Class'; ?>
                        </button>

                        <?php if ($editClass): ?>
                            <a href="classes.php" class="text-link">Cancel edit</a>
                        <?php endif; ?>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Class List</h2>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Class Name</th>
                                    <th>Class Teacher</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$classes): ?>
                                    <tr>
                                        <td colspan="4">No classes found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($classes as $index => $class): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($class['class_name']); ?></td>
                                        <td><?= e($class['class_teacher_name'] ?? 'Not assigned'); ?></td>
                                        <td class="table-actions">
                                            <a href="classes.php?edit=<?= e((string) $class['id']); ?>">Edit</a>
                                            <form action="delete-class.php" method="POST">
                                                <input type="hidden" name="id" value="<?= e((string) $class['id']); ?>">
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
