<?php

namespace App\Core;

class View
{
    /**
     * Renderiza una vista dentro del layout autenticado.
     */
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $viewFile   = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = VIEW_PATH . '/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            Response::abort(404, "Vista no encontrada: $view");
        }

        // Capturamos el contenido de la vista
        ob_start();
        extract($data, EXTR_SKIP);
        include $viewFile;
        $content = ob_get_clean();

        // Inyectamos en el layout
        if (file_exists($layoutFile)) {
            extract($data, EXTR_SKIP);
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Renderiza una vista sin layout (para partials o respuestas AJAX HTML).
     */
    public static function partial(string $view, array $data = []): string
    {
        $file = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            return '';
        }

        ob_start();
        extract($data, EXTR_SKIP);
        include $file;
        return ob_get_clean();
    }

    /**
     * Escapa una cadena para salida HTML segura.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
