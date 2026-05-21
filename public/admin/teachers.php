<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$editTeacher = null;
$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

if (isset($_GET['edit'])) {
    $statement = $database->prepare(
        'SELECT teachers.*, users.full_name, users.email, users.phone, users.status
         FROM teachers
         INNER JOIN users ON users.id = teachers.user_id
         WHERE teachers.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => (int) $_GET['edit']]);
    $editTeacher = $statement->fetch();
}

$whereSql = '';
$params = [];

if ($search !== '') {
    $whereSql = 'WHERE users.full_name LIKE :search
                 OR users.email LIKE :search
                 OR teachers.staff_number LIKE :search
                 OR users.phone LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$countStatement = $database->prepare(
    "SELECT COUNT(*)
     FROM teachers
     INNER JOIN users ON users.id = teachers.user_id
     $whereSql"
);
$countStatement->execute($params);
$totalTeachers = (int) $countStatement->fetchColumn();
$totalPages = max(1, (int) ceil($totalTeachers / $perPage));

$teacherStatement = $database->prepare(
    "SELECT teachers.*, users.full_name, users.email, users.phone, users.status
     FROM teachers
     INNER JOIN users ON users.id = teachers.user_id
     $whereSql
     ORDER BY users.full_name ASC
     LIMIT :limit OFFSET :offset"
);

foreach ($params as $key => $value) {
    $teacherStatement->bindValue(':' . $key, $value);
}

$teacherStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
$teacherStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
$teacherStatement->execute();
$teachers = $teacherStatement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - <?= e(app_config('app_name')); ?></title>
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
                <a href="teachers.php" class="active">Teachers</a>
                <a href="classes.php">Classes</a>
                <a href="subjects.php">Subjects</a>
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
                    <h1>Manage Teachers</h1>
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
                    <h2><?= $editTeacher ? 'Edit Teacher' : 'Add Teacher'; ?></h2>
                    <form action="save-teacher.php" method="POST" class="stack-form">
                        <input type="hidden" name="id" value="<?= e((string) ($editTeacher['id'] ?? '')); ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?= e($editTeacher['full_name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= e($editTeacher['email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="<?= e($editTeacher['phone'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="staff_number">Staff Number</label>
                                <input type="text" id="staff_number" name="staff_number" value="<?= e($editTeacher['staff_number'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select gender</option>
                                    <?php foreach (['male', 'female', 'other'] as $gender): ?>
                                        <option value="<?= e($gender); ?>" <?= (($editTeacher['gender'] ?? '') === $gender) ? 'selected' : ''; ?>>
                                            <?= e(ucfirst($gender)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="qualification">Qualification</label>
                                <input type="text" id="qualification" name="qualification" value="<?= e($editTeacher['qualification'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="employment_date">Employment Date</label>
                                <input type="date" id="employment_date" name="employment_date" value="<?= e($editTeacher['employment_date'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="password">Password <?= $editTeacher ? '(leave blank to keep current)' : ''; ?></label>
                                <input type="password" id="password" name="password" <?= $editTeacher ? '' : 'required'; ?>>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?= e($editTeacher['address'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <?= $editTeacher ? 'Update Teacher' : 'Save Teacher'; ?>
                        </button>

                        <?php if ($editTeacher): ?>
                            <a href="teachers.php" class="text-link">Cancel edit</a>
                        <?php endif; ?>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Teacher List</h2>

                    <form method="GET" action="teachers.php" class="search-form">
                        <input type="text" name="search" placeholder="Search teachers..." value="<?= e($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search !== ''): ?>
                            <a href="teachers.php" class="text-link">Clear</a>
                        <?php endif; ?>
                    </form>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Staff No.</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$teachers): ?>
                                    <tr>
                                        <td colspan="7">No teachers found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($teachers as $index => $teacher): ?>
                                    <tr>
                                        <td><?= e((string) ($offset + $index + 1)); ?></td>
                                        <td><?= e($teacher['full_name']); ?></td>
                                        <td><?= e($teacher['email']); ?></td>
                                        <td><?= e($teacher['staff_number']); ?></td>
                                        <td><?= e($teacher['phone'] ?? ''); ?></td>
                                        <td><?= e(ucfirst($teacher['status'])); ?></td>
                                        <td class="table-actions">
                                            <a href="teachers.php?edit=<?= e((string) $teacher['id']); ?>">Edit</a>
                                            <form action="delete-teacher.php" method="POST">
                                                <input type="hidden" name="id" value="<?= e((string) $teacher['id']); ?>">
                                                <button type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a
                                href="teachers.php?page=<?= e((string) $i); ?>&search=<?= e(urlencode($search)); ?>"
                                class="<?= $i === $page ? 'active' : ''; ?>"
                            >
                                <?= e((string) $i); ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </article>
            </section>
        </section>
    </main>
</body>
</html>
