<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$editStudent = null;

if (isset($_GET['edit'])) {
    $statement = $database->prepare(
        'SELECT students.*, users.full_name, users.email, users.phone, users.status
         FROM students
         INNER JOIN users ON users.id = students.user_id
         WHERE students.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => (int) $_GET['edit']]);
    $editStudent = $statement->fetch();
}

$classes = $database->query('SELECT * FROM classes ORDER BY class_name ASC')->fetchAll();
$students = $database
    ->query('SELECT students.*, users.full_name, users.email, users.phone, users.status, classes.class_name
             FROM students
             INNER JOIN users ON users.id = students.user_id
             INNER JOIN classes ON classes.id = students.class_id
             ORDER BY users.full_name ASC')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - <?= e(app_config('app_name')); ?></title>
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
                <a href="students.php" class="active">Students</a>
                <a href="teachers.php">Teachers</a>
                <a href="classes.php">Classes</a>
                <a href="subjects.php">Subjects</a>
                <a href="class-subjects.php">Class Subjects</a>
                <a href="teacher-subjects.php">Teacher Subjects</a>
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
                    <h1>Manage Students</h1>
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

            <section class="wide-management-grid">
                <article class="content-panel">
                    <h2><?= $editStudent ? 'Edit Student' : 'Add Student'; ?></h2>
                    <form action="save-student.php" method="POST" class="stack-form">
                        <input type="hidden" name="id" value="<?= e((string) ($editStudent['id'] ?? '')); ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?= e($editStudent['full_name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= e($editStudent['email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="<?= e($editStudent['phone'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="admission_number">Admission Number</label>
                                <input type="text" id="admission_number" name="admission_number" value="<?= e($editStudent['admission_number'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="class_id">Class</label>
                                <select id="class_id" name="class_id" required>
                                    <option value="">Select class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= e((string) $class['id']); ?>" <?= ((string) ($editStudent['class_id'] ?? '') === (string) $class['id']) ? 'selected' : ''; ?>>
                                            <?= e($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select gender</option>
                                    <?php foreach (['male', 'female', 'other'] as $gender): ?>
                                        <option value="<?= e($gender); ?>" <?= (($editStudent['gender'] ?? '') === $gender) ? 'selected' : ''; ?>>
                                            <?= e(ucfirst($gender)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" value="<?= e($editStudent['date_of_birth'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="admission_date">Admission Date</label>
                                <input type="date" id="admission_date" name="admission_date" value="<?= e($editStudent['admission_date'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="guardian_name">Guardian Name</label>
                                <input type="text" id="guardian_name" name="guardian_name" value="<?= e($editStudent['guardian_name'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="guardian_phone">Guardian Phone</label>
                                <input type="text" id="guardian_phone" name="guardian_phone" value="<?= e($editStudent['guardian_phone'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="password">Password <?= $editStudent ? '(leave blank to keep current)' : ''; ?></label>
                                <input type="password" id="password" name="password" <?= $editStudent ? '' : 'required'; ?>>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?= e($editStudent['address'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <?= $editStudent ? 'Update Student' : 'Save Student'; ?>
                        </button>

                        <?php if ($editStudent): ?>
                            <a href="students.php" class="text-link">Cancel edit</a>
                        <?php endif; ?>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Student List</h2>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Admission No.</th>
                                    <th>Class</th>
                                    <th>Guardian</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$students): ?>
                                    <tr>
                                        <td colspan="7">No students found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($student['full_name']); ?></td>
                                        <td><?= e($student['email']); ?></td>
                                        <td><?= e($student['admission_number']); ?></td>
                                        <td><?= e($student['class_name']); ?></td>
                                        <td><?= e($student['guardian_name'] ?? ''); ?></td>
                                        <td class="table-actions">
                                            <a href="students.php?edit=<?= e((string) $student['id']); ?>">Edit</a>
                                            <form action="delete-student.php" method="POST">
                                                <input type="hidden" name="id" value="<?= e((string) $student['id']); ?>">
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
