<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/Connection.php';

$appName = app_config('app_name');

try {
    $database = Connection::connect();
    $connectionStatus = 'Database connection successful.';
} catch (PDOException $exception) {
    $connectionStatus = 'Database connection failed: ' . $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($appName); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="setup-page">
        <section class="setup-panel">
            <p class="eyebrow">Setup Check</p>
            <h1><?= e($appName); ?></h1>
            <p class="lead">
                The project configuration has been created. This page checks whether PHP can connect to MySQL.
            </p>
            <div class="status-box">
                <?= e($connectionStatus); ?>
            </div>
        </section>
    </main>
</body>
</html>
