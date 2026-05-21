<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/Connection.php';

try {
    $database = Connection::connect();
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);

    $statement = $database->prepare(
        'UPDATE users SET password = :password WHERE email = :email'
    );

    $statement->execute([
        'password' => $hashedPassword,
        'email' => 'admin@example.com',
    ]);

    $message = 'Admin password has been reset successfully. You can now log in with admin@example.com and admin123.';
} catch (PDOException $exception) {
    $message = 'Password reset failed: ' . $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="setup-page">
        <section class="setup-panel">
            <p class="eyebrow">One-Time Setup</p>
            <h1>Admin Password Reset</h1>
            <div class="status-box">
                <?= e($message); ?>
            </div>
            <p class="lead">
                After logging in successfully, delete this file for security.
            </p>
            <a href="login.php" class="btn btn-primary">Go to Login</a>
        </section>
    </main>
</body>
</html>

