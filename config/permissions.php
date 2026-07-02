<?php

/**
 * Matriz de permisos por rol.
 * Formato: 'rol' => ['modulo.accion', ...]
 */
return [
    'admin' => [
        'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar',
        'pagos.ver', 'pagos.registrar', 'pagos.anular',
        'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
        'planes.ver', 'planes.crear', 'planes.editar',
        'config.ver', 'config.editar',
        'reportes.ver',
        'logs.ver',
    ],
    'operador' => [
        'clientes.ver', 'clientes.crear', 'clientes.editar',
        'pagos.ver', 'pagos.registrar',
        'planes.ver',
        'reportes.ver',
    ],
    'cajero' => [
        'clientes.ver',
        'pagos.ver', 'pagos.registrar',
        'reportes.ver',
    ],
    'tecnico' => [
        'clientes.ver',
        'pagos.ver',
    ],
];
