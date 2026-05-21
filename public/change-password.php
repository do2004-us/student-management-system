<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../app/Helpers/auth.php';

require_login();

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$dashboardUrl = '/student-management-system/public/login.php';

if (current_user()['role'] === 'admin') {
    $dashboardUrl = '/student-management-system/public/admin/dashboard.php';
} elseif (current_user()['role'] === 'teacher') {
    $dashboardUrl = '/student-management-system/public/teacher/dashboard.php';
} elseif (current_user()['role'] === 'student') {
    $dashboardUrl = '/student-management-system/public/student/dashboard.php';
} elseif (current_user()['role'] === 'parent') {
    $dashboardUrl = '/student-management-system/public/parent/dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <p class="eyebrow">Account Security</p>
            <h1>Change Password</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error); ?></div>
            <?php endif; ?>

            <form action="process-change-password.php" method="POST" class="auth-form">
                <?= csrf_field(); ?>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="6" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
                <a href="<?= e($dashboardUrl); ?>" class="text-link">Back to dashboard</a>
            </form>
        </section>
    </main>
</body>
</html>
