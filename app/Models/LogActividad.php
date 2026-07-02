<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Auth;

class LogActividad extends Model
{
    protected string $table = 'logs';

    public function registrar(string $accion, string $entidad = '', ?int $entidadId = null, ?array $datos = null): void
    {
        $this->insert([
            'usuario_id' => Auth::id(),
            'accion'     => $accion,
            'entidad'    => $entidad,
            'entidad_id' => $entidadId,
            'datos_json' => $datos ? json_encode($datos, JSON_UNESCAPED_UNICODE) : null,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }
}
