<?php

/*
|--------------------------------------------------------------------------
| Global Helper Functions
|--------------------------------------------------------------------------
| Helper functions are small reusable functions used in many files.
*/

function app_config(string $key, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/../../config/app.php';
    }

    return $config[$key] ?? $default;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

