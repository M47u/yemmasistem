<?php

namespace App\Core;

class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $path, int $status = 302): never
    {
        $base = rtrim(defined('APP_URL') ? APP_URL : '', '/');
        $url  = str_starts_with($path, 'http') ? $path : $base . $path;
        header('Location: ' . $url, true, $status);
        exit;
    }

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);

        $view = match($status) {
            403     => '403',
            404     => '404',
            default => '500',
        };

        if (!$message) {
            $message = match($status) {
                403 => 'Acceso denegado.',
                404 => 'Página no encontrada.',
                default => 'Error interno del servidor.',
            };
        }

        // Intentar renderizar la vista de error; si falla, texto plano
        $file = VIEW_PATH . '/errors/' . $view . '.php';
        if (file_exists($file)) {
            extract(['message' => $message, 'code' => $status]);
            include $file;
        } else {
            echo "<h1>$status</h1><p>" . htmlspecialchars($message) . "</p>";
        }

        exit;
    }

    public static function setSecurityHeaders(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; img-src 'self' data:;");
    }
}
