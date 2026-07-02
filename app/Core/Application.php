<?php

namespace App\Core;

class Application
{
    private Router $router;

    public function __construct()
    {
        $this->loadEnv();
        $this->defineConstants();
        $this->configurePhp();
        $this->router = new Router();
    }

    private function loadEnv(): void
    {
        $file = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }

    private function defineConstants(): void
    {
        $cfg = require dirname(__DIR__, 2) . '/config/app.php';

        define('APP_NAME',    $cfg['name']);
        define('APP_ENV',     $cfg['env']);
        define('APP_DEBUG',   $cfg['debug']);
        define('APP_URL',     $cfg['url']);
        define('APP_VERSION', $cfg['version']);

        define('ROOT_PATH',   dirname(__DIR__, 2));
        define('CONFIG_PATH', ROOT_PATH . '/config');
        define('VIEW_PATH',   ROOT_PATH . '/app/Views');
        define('STORAGE_PATH', ROOT_PATH . '/storage');
    }

    private function configurePhp(): void
    {
        $cfg = require CONFIG_PATH . '/app.php';
        date_default_timezone_set($cfg['timezone']);

        if (APP_DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', STORAGE_PATH . '/logs/php_errors.log');
        }
    }

    public function run(): void
    {
        Response::setSecurityHeaders();
        Session::start();

        $request = new Request();

        // Ejecutar middlewares globales
        (new \App\Middleware\CSRFMiddleware())->handle($request);
        (new \App\Middleware\AuthMiddleware())->handle($request);

        // Cargar rutas y despachar
        $router = $this->router;
        require ROOT_PATH . '/routes/web.php';
        $router->dispatch($request);
    }
}
