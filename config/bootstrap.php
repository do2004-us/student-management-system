<?php

/*
|--------------------------------------------------------------------------
| Application Bootstrap File
|--------------------------------------------------------------------------
| This file starts important system services before any page is loaded.
*/

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/Helpers/functions.php';

date_default_timezone_set(app_config('timezone', 'UTC'));

/*
|--------------------------------------------------------------------------
| Error Reporting
|--------------------------------------------------------------------------
| During development, showing errors helps us learn and fix mistakes.
| In production hosting, this should be turned off and errors should be logged.
*/

ini_set('display_errors', '1');
error_reporting(E_ALL);

