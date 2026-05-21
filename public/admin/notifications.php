<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

$database = Connection::connect();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$users = $database
    ->query('SELECT users.id, users.full_name, users.email, roles.name AS role
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.status = "active"
             ORDER BY roles.name ASC, users.full_name ASC')
    ->fetchAll();

$notifications = $database
    ->query('SELECT notifications.*, users.full_name
             FROM notifications
             INNER JOIN users ON users.id = notifications.user_id
             ORDER BY notifications.created_at DESC
             LIMIT 50')
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="app-layout">
        <?php require __DIR__ . '/../../app/Views/layouts/admin-sidebar.php'; ?>

        <section class="main-area">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1>Notifications</h1>
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
                    <h2>Send Notification</h2>
                    <form action="save-notification.php" method="POST" class="stack-form">
                        <?= csrf_field(); ?>
                        <div class="form-group">
                            <label for="user_id">Recipient</label>
                            <select id="user_id" name="user_id" required>
                                <option value="">Select user</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= e((string) $user['id']); ?>">
                                        <?= e($user['full_name'] . ' - ' . ucfirst($user['role'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Send Notification</button>
                    </form>
                </article>

                <article class="content-panel">
                    <h2>Recent Notifications</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Recipient</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$notifications): ?>
                                    <tr>
                                        <td colspan="5">No notifications found.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($notifications as $index => $notification): ?>
                                    <tr>
                                        <td><?= e((string) ($index + 1)); ?></td>
                                        <td><?= e($notification['full_name']); ?></td>
                                        <td><?= e($notification['title']); ?></td>
                                        <td><?= (int) $notification['is_read'] === 1 ? 'Read' : 'Unread'; ?></td>
                                        <td><?= e($notification['created_at']); ?></td>
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
