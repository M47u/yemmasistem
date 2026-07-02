<?php

namespace App\Models;

use App\Core\Model;

class HistorialEstado extends Model
{
    protected string $table = 'historial_estados';

    public function registrar(int $clienteId, string $estadoAnterior, string $estadoNuevo, string $motivo, ?int $usuarioId = null): int
    {
        return $this->insert([
            'cliente_id'       => $clienteId,
            'estado_anterior'  => $estadoAnterior,
            'estado_nuevo'     => $estadoNuevo,
            'motivo'           => $motivo,
            'usuario_id'       => $usuarioId,
        ]);
    }

    public function porCliente(int $clienteId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
             FROM historial_estados h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.cliente_id = ?
             ORDER BY h.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$clienteId, $limit]);
        return $stmt->fetchAll();
    }
}
