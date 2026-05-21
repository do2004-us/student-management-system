<?php

require_once __DIR__ . '/../config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <p class="eyebrow">Password Help</p>
            <h1>Forgot password</h1>
            <p class="lead">
                The reset email feature will be connected later. For now, ask the administrator to reset your password.
            </p>
            <a href="login.php" class="btn btn-primary">Back to Login</a>
        </section>
    </main>
</body>
</html>

