<?php

/*
|--------------------------------------------------------------------------
| Authentication Helper Functions
|--------------------------------------------------------------------------
| These functions help us manage login, logout, and protected pages.
*/

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/student-management-system/public/login.php');
    }
}

function require_role(string $role): void
{
    require_login();

    if (current_user()['role'] !== $role) {
        redirect('/student-management-system/public/login.php');
    }
}

