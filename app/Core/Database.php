<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::connect();
        }
        return self::$instance;
    }

    private static function connect(): PDO
    {
        $cfg = require CONFIG_PATH . '/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset']
        );

        try {
            return new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
        } catch (PDOException $e) {
            // En debug mostramos detalle; en producción, mensaje genérico
            $msg = defined('APP_DEBUG') && APP_DEBUG
                ? 'Database error: ' . $e->getMessage()
                : 'No se pudo conectar a la base de datos.';
            throw new RuntimeException($msg, 500, $e);
        }
    }

    public static function pdo(): PDO
    {
        return self::getInstance();
    }
}
