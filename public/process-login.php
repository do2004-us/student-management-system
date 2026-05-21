<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/Connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student-management-system/public/login.php');
}

verify_csrf_token();

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter both email and password.';
    redirect('/student-management-system/public/login.php');
}

try {
    $database = Connection::connect();

    $statement = $database->prepare(
        'SELECT users.id, users.full_name, users.email, users.password, users.status, roles.name AS role
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE users.email = :email
         LIMIT 1'
    );

    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Invalid email or password.';
        redirect('/student-management-system/public/login.php');
    }

    if ($user['status'] !== 'active') {
        $_SESSION['login_error'] = 'Your account is not active. Please contact the school administrator.';
        redirect('/student-management-system/public/login.php');
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    $updateLogin = $database->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
    $updateLogin->execute(['id' => $user['id']]);

    if ($user['role'] === 'admin') {
        redirect('/student-management-system/public/admin/dashboard.php');
    }

    if ($user['role'] === 'teacher') {
        redirect('/student-management-system/public/teacher/dashboard.php');
    }

    if ($user['role'] === 'parent') {
        redirect('/student-management-system/public/parent/dashboard.php');
    }

    redirect('/student-management-system/public/student/dashboard.php');
} catch (PDOException $exception) {
    $_SESSION['login_error'] = 'Login failed. Please try again later.';
    redirect('/student-management-system/public/login.php');
}
