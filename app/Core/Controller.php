<?php

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function redirect(string $path): never
    {
        Response::redirect($path);
    }

    protected function abort(int $code, string $message = ''): never
    {
        Response::abort($code, $message);
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash('flash_type', $type);
        Session::flash('flash_message', $message);
    }

    protected function requirePermission(string $permission): void
    {
        if (!Auth::can($permission)) {
            Response::abort(403);
        }
    }
}
