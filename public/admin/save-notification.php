<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/Connection.php';
require_once __DIR__ . '/../../app/Helpers/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/admin/notifications.php');
}

verify_csrf_token();

$userId = (int) ($_POST['user_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($userId <= 0 || $title === '' || $message === '') {
    $_SESSION['error'] = 'Recipient, title, and message are required.';
    redirect('/student-management-system/public/admin/notifications.php');
}

try {
    $database = Connection::connect();
    $statement = $database->prepare(
        'INSERT INTO notifications (user_id, title, message)
         VALUES (:user_id, :title, :message)'
    );

    $statement->execute([
        'user_id' => $userId,
        'title' => $title,
        'message' => $message,
    ]);

    $_SESSION['success'] = 'Notification sent successfully.';
} catch (PDOException $exception) {
    $_SESSION['error'] = 'Unable to send notification.';
}

redirect('/student-management-system/public/admin/notifications.php');
