<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class OfflineController extends Controller
{
    public function index(Request $request): void
    {
        // La página offline es servida directamente por el Service Worker
        // Esta ruta es fallback cuando el SW no puede servir el archivo
        http_response_code(200);
        $file = dirname(__DIR__, 2) . '/public/offline.html';
        if (file_exists($file)) {
            readfile($file);
        } else {
            echo '<!DOCTYPE html><html><body><h1>Sin conexión</h1></body></html>';
        }
        exit;
    }
}
