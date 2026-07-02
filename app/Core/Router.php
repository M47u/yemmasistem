<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method;
        $path   = $request->path;   // Ya normalizado en Request::__construct()

        // Buscamos coincidencia exacta primero, luego patrones con parámetros
        $handler = $this->match($method, $path);

        if ($handler === null) {
            Response::abort(404);
        }

        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass)) {
            Response::abort(500, "Controller no encontrado: $controllerClass");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            Response::abort(500, "Acción no encontrada: $action");
        }

        $controller->$action($request, ...$handler['params'] ?? []);
    }

    private function match(string $method, string $path): ?array
    {
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $handler) {
            $params = [];
            $regex  = $this->patternToRegex($pattern, $params);

            if (preg_match($regex, $path, $matches)) {
                [$class, $action] = explode('@', $handler);
                $class = 'App\\Controllers\\' . $class;

                $namedParams = array_filter(
                    array_intersect_key($matches, array_flip($params)),
                    'is_string',
                    ARRAY_FILTER_USE_KEY
                );

                return [$class, $action, 'params' => array_values($namedParams)];
            }
        }

        return null;
    }

    private function patternToRegex(string $pattern, array &$params): string
    {
        // Separamos placeholders de las partes literales para escapar solo las literales
        $segments = preg_split('/(\{[^}]+\})/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE);
        $regex    = '';
        foreach ($segments as $seg) {
            if (preg_match('/^\{(\w+)\}$/', $seg, $m)) {
                $params[] = $m[1];
                $regex   .= '(?P<' . $m[1] . '>[^/]+)';
            } else {
                $regex .= preg_quote($seg, '#');
            }
        }
        return '#^' . $regex . '$#';
    }

    private function normalizePath(string $path): string
    {
        // Elimina el prefijo de subdirectorio si se ejecuta en XAMPP
        $base = parse_url(APP_URL, PHP_URL_PATH) ?? '';
        if ($base && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        return '/' . trim($path, '/') ?: '/';
    }
}
