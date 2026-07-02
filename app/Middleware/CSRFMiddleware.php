<?php

namespace App\Middleware;

use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;

class CSRFMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request): void
    {
        if (in_array($request->method, self::SAFE_METHODS, true)) {
            return;
        }

        // Las rutas de API JSON tienen su propio mecanismo
        if ($request->isAjax() && $this->hasValidAjaxHeader($request)) {
            return;
        }

        $token = $request->csrfToken();

        if (!$token || !CSRF::validate($token)) {
            if ($request->isAjax()) {
                Response::json(['error' => 'Token CSRF inválido.'], 419);
            }
            Response::abort(419, 'Token CSRF inválido o expirado. Recargá la página e intentá de nuevo.');
        }
    }

    private function hasValidAjaxHeader(Request $request): bool
    {
        // Verificamos el header X-CSRF-Token enviado desde JS
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return $header && CSRF::validate($header);
    }
}
