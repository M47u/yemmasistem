<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class RoleMiddleware
{
    public function handle(Request $request, string $permission): void
    {
        if (!Auth::can($permission)) {
            if ($request->isAjax()) {
                Response::json(['error' => 'Sin permisos para esta acción.'], 403);
            }
            Response::abort(403);
        }
    }
}
