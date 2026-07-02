<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class AuthMiddleware
{
    // Rutas que no requieren autenticación
    private const PUBLIC_PATHS = ['/login', '/offline', '/manifest.json', '/icons/', '/sw.js'];

    public function handle(Request $request): void
    {
        $path = $request->path;

        // Rutas públicas y assets estáticos no requieren auth
        if ($this->isPublic($path)) {
            return;
        }

        if (!Auth::check()) {
            if ($request->isAjax()) {
                Response::json(['error' => 'No autenticado.', 'redirect' => APP_URL . '/login'], 401);
            }
            Response::redirect('/login');
        }
    }

    private function isPublic(string $path): bool
    {
        foreach (self::PUBLIC_PATHS as $public) {
            if (str_starts_with($path, $public)) {
                return true;
            }
        }
        return false;
    }
}
