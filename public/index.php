<?php

declare(strict_types=1);

// Autoloader manual — sin Composer
spl_autoload_register(function (string $class): void {
    // Convierte App\Core\Database → /app/Core/Database.php
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = str_replace(['App\\', '\\'], ['', '/'], $class);
    $file     = dirname(__DIR__) . '/app/' . $relative . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Helpers globales
require_once dirname(__DIR__) . '/app/Helpers/functions.php';

// Arrancar la aplicación
(new App\Core\Application())->run();
