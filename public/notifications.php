<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../app/Helpers/auth.php';

require_login();

$database = Connection::connect();

$statement = $database->prepare(
    'SELECT * FROM notifications
     WHERE user_id = :user_id
     ORDER BY created_at DESC'
);
$statement->execute(['user_id' => current_user()['id']]);
$notifications = $statement->fetchAll();

$markRead = $database->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id');
$markRead->execute(['user_id' => current_user()['id']]);

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
    <title>Notifications - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="student-layout">
        <header class="student-header">
            <div>
                <p class="eyebrow">Messages</p>
                <h1>Notifications</h1>
                <p>View messages sent by the school administrator.</p>
            </div>
            <div class="header-actions">
                <a href="<?= e($dashboardUrl); ?>" class="btn btn-light">Dashboard</a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
        </header>

        <section class="content-panel">
            <h2>My Notifications</h2>

            <div class="mini-list">
                <?php if (!$notifications): ?>
                    <p>No notifications found.</p>
                <?php endif; ?>

                <?php foreach ($notifications as $notification): ?>
                    <div>
                        <strong><?= e($notification['title']); ?></strong>
                        <span><?= e($notification['message']); ?></span>
                        <small><?= e($notification['created_at']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
