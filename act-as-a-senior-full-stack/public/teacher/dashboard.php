<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('teacher');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="dashboard-page">
        <section class="dashboard-shell">
            <h1>Teacher Dashboard</h1>
            <p>Welcome, <?= e(current_user()['full_name']); ?>.</p>
            <a href="../logout.php" class="btn btn-primary">Logout</a>
        </section>
    </main>
</body>
</html>

