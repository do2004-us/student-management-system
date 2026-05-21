<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$students = $database
    ->query('SELECT students.id, students.admission_number, users.full_name, classes.class_name
             FROM students
             INNER JOIN users ON users.id = students.user_id
             INNER JOIN classes ON classes.id = students.class_id
             ORDER BY users.full_name ASC')
    ->fetchAll();

$parents = $database
    ->query('SELECT parents.id, parents.occupation, users.full_name, users.email, users.phone,
                    GROUP_CONCAT(CONCAT(student_user.full_name, " (", parent_students.relationship, ")") SEPARATOR ", ") AS children
             FROM parents
             INNER JOIN users ON users.id = parents.user_id
             LEFT JOIN parent_students ON parent_students.parent_id = parents.id
             LEFT JOIN students ON students.id = parent_students.student_id
             LEFT JOIN users AS student_user ON student_user.id = students.user_id
             GROUP BY parents.id
             ORDER BY users.full_name ASC')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Parents - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/premium-dashboard.css">
</head>
<body>
    <main class="app-layout">
        <?php require __DIR__ . '/../../app/Views/layouts/admin-sidebar.php'; ?>

        <section class="main-area">
            <header class="topbar">
                <button type="button" class="icon-btn" data-mobile-nav aria-label="Open navigation">☰</button>
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Manage Parents</h1>
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
                    <h2>Add Parent</h2>
                    <form action="save-parent.php" method="POST" class="stack-form">
                        <?= csrf_field(); ?>

                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone">
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" minlength="6" required>
                        </div>

                        <div class="form-group">
                            <label for="student_id">Linked Student</label>
                            <select id="student_id" name="student_id" required>
                                <option value="">Select student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= e((string) $student['id']); ?>">
                                        <?= e($student['full_name'] . ' - ' . $student['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="relationship">Relationship</label>
                            <input type="text" id="relationship" name="relationship" value="Guardian" required>
                        </div>

                        <div class="form-group">
                            <label for="occupation">Occupation</label>
                            <input type="text" id="occupation" name="occupation">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Parent</button>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Parent List</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Children</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$parents): ?>
                                    <tr>
                                        <td colspan="5">No parents found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($parents as $index => $parent): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($parent['full_name']); ?></td>
                                        <td><?= e($parent['email']); ?></td>
                                        <td><?= e($parent['phone'] ?? ''); ?></td>
                                        <td><?= e($parent['children'] ?? 'No linked student'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </section>
    </main>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>

