<?php

/** @var \App\Core\Router $router */

// Autenticación
$router->get('/login',  'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

// Dashboard
$router->get('/',         'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Clientes
$router->get('/clientes',             'ClienteController@index');
$router->post('/clientes',            'ClienteController@store');
$router->get('/clientes/{id}',        'ClienteController@show');
$router->post('/clientes/{id}',       'ClienteController@update');
$router->post('/clientes/{id}/baja',  'ClienteController@destroy');

// Pagos
$router->post('/pagos/toggle',            'PagoController@toggle');
$router->post('/pagos',                   'PagoController@store');
$router->get('/pagos/historial/{id}',     'PagoController@historial');

// Usuarios (solo admin)
$router->get('/usuarios',        'UsuarioController@index');
$router->post('/usuarios',       'UsuarioController@store');
$router->post('/usuarios/{id}',  'UsuarioController@update');

// Offline PWA
$router->get('/offline', 'OfflineController@index');

// PWA: manifest e íconos (servidos dinámicamente por PHP)
$router->get('/manifest.json',       'ManifestController@serve');
$router->get('/icons/{name}.png',    'IconController@serve');
