<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/Connection.php';

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <div class="auth-brand">
                <span class="brand-mark">SMS</span>
                <div>
                    <p class="eyebrow">Welcome Back</p>
                    <h1>Sign in</h1>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= e($error); ?>
                </div>
            <?php endif; ?>

            <form action="process-login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@example.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">Sign In</button>

                <a href="forgot-password.php" class="text-link">Forgot password?</a>
            </form>
        </section>
    </main>
</body>
</html>

