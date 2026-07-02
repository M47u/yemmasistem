<?php

$url = $_ENV['APP_URL'] ?? 'https://yemmasistem.pyfsasoftware.com.ar';

return [
    'name'     => $_ENV['APP_NAME']  ?? 'Yemma ISP',
    'env'      => $_ENV['APP_ENV']   ?? 'production',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'      => $url,
    'timezone' => 'America/Argentina/Buenos_Aires',
    'locale'   => 'es_AR',
    'version'  => '1.0.0',

    'session' => [
        'name'     => 'yemma_session',
        'lifetime' => 480,          // minutos
        'secure'   => filter_var($_ENV['APP_SESSION_SECURE'] ?? (parse_url($url, PHP_URL_SCHEME) === 'https'), FILTER_VALIDATE_BOOLEAN),
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    'upload' => [
        'max_size'   => 5 * 1024 * 1024,   // 5 MB
        'allowed'    => ['jpg', 'jpeg', 'png', 'pdf'],
        'path'       => __DIR__ . '/../public/uploads/',
    ],
];
