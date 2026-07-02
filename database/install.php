<?php

/**
 * Script de instalación de la base de datos.
 * Ejecutar una sola vez: php database/install.php
 * O acceder desde el browser: http://localhost/YemmaSistem/database/install.php
 * (Borrar este archivo después de la instalación en producción)
 */

define('ROOT_PATH',   dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar .env
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

$cfg = require CONFIG_PATH . '/database.php';

try {
    // Conexión sin seleccionar BD para crearla si no existe
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};charset={$cfg['charset']}",
        $cfg['username'],
        $cfg['password'],
        $cfg['options']
    );

    $db = $cfg['database'];
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    echo "<h2>Instalando base de datos: $db</h2>";

    // Ejecutar migraciones
    $migrations = glob(ROOT_PATH . '/database/migrations/*.sql');
    sort($migrations);
    foreach ($migrations as $file) {
        $sql = file_get_contents($file);
        $pdo->exec($sql);
        echo "<p>✅ " . basename($file) . "</p>";
    }

    // Ejecutar seeds
    $seeds = glob(ROOT_PATH . '/database/seeds/*.sql');
    sort($seeds);
    foreach ($seeds as $file) {
        $sql = file_get_contents($file);
        $pdo->exec($sql);
        echo "<p>🌱 " . basename($file) . "</p>";
    }

    echo "<h3>✅ Instalación completada.</h3>";
    echo "<p><strong>Usuario:</strong> admin@yemma.local</p>";
    echo "<p><strong>Contraseña:</strong> Admin1234</p>";
    echo "<p style='color:red'><strong>⚠ Cambiá la contraseña inmediatamente.</strong></p>";
    echo "<p><a href='/YemmaSistem/'>Ir al sistema →</a></p>";

} catch (PDOException $e) {
    echo "<h3>❌ Error de instalación:</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
