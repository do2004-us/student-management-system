<?php

/*
|--------------------------------------------------------------------------
| Database Connection Class
|--------------------------------------------------------------------------
| This class creates a secure PDO connection to MySQL.
| PDO supports prepared statements, which protect against SQL injection.
*/

declare(strict_types=1);

class Connection
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection === null) {
            $config = require __DIR__ . '/database_config.php';

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['database'],
                $config['charset']
            );

            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$connection;
    }
}

