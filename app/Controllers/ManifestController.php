<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class ManifestController extends Controller
{
    public function serve(Request $request): void
    {
        $base  = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
        $name  = APP_NAME;

        $manifest = [
            'name'             => $name,
            'short_name'       => 'Yemma',
            'description'      => 'Sistema de gestión para proveedor de Internet',
            'start_url'        => $base . '/',
            'scope'            => $base . '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait-primary',
            'theme_color'      => '#0D4A77',
            'background_color' => '#DFE9F0',
            'lang'             => 'es',
            'dir'              => 'ltr',
            'icons'            => [
                [
                    'src'     => $base . '/icons/icon-192.png',
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'     => $base . '/icons/icon-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'     => $base . '/icons/icon-maskable.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
            'shortcuts' => [
                [
                    'name'      => 'Clientes',
                    'short_name' => 'Clientes',
                    'url'       => $base . '/clientes',
                    'icons'     => [['src' => $base . '/icons/icon-192.png', 'sizes' => '192x192']],
                ],
            ],
            'categories' => ['business', 'productivity'],
        ];

        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }
}
