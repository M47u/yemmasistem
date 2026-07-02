<?php

namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    protected string $table = 'clientes';

    /**
     * Lista de clientes para la pantalla principal.
     * Incluye si pagó en el período dado y datos del plan.
     */
    public function listaPorPeriodo(int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.numero_interno, c.nombre, c.apellido, c.telefono,
                    c.whatsapp, c.precio_mensual, c.estado, c.observaciones,
                    p.nombre AS plan_nombre, p.velocidad_mb,
                    pag.id AS pago_id, pag.fecha_pago, pag.importe AS pago_importe,
                    pag.metodo_pago_id
             FROM clientes c
             LEFT JOIN planes p ON p.id = c.plan_id
             LEFT JOIN pagos pag ON pag.cliente_id = c.id
                 AND pag.periodo_año = ? AND pag.periodo_mes = ?
             WHERE c.deleted_at IS NULL
             ORDER BY c.apellido ASC, c.nombre ASC'
        );
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll();
    }

    public function buscar(string $query): array
    {
        $like  = '%' . $query . '%';
        $stmt  = $this->db->prepare(
            'SELECT c.*, p.nombre AS plan_nombre
             FROM clientes c
             LEFT JOIN planes p ON p.id = c.plan_id
             WHERE c.deleted_at IS NULL AND (
                 c.nombre          LIKE ? OR
                 c.apellido        LIKE ? OR
                 c.dni             LIKE ? OR
                 c.telefono        LIKE ? OR
                 c.whatsapp        LIKE ? OR
                 c.direccion       LIKE ? OR
                 CAST(c.numero_interno AS CHAR) LIKE ?
             )
             ORDER BY c.apellido, c.nombre
             LIMIT 50'
        );
        $stmt->execute(array_fill(0, 7, $like));
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, p.nombre AS plan_nombre, p.velocidad_mb
             FROM clientes c
             LEFT JOIN planes p ON p.id = c.plan_id
             WHERE c.id = ? AND c.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $data['numero_interno'] = $this->nextNumeroInterno();
        return $this->insert($data);
    }

    public function updateCliente(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function softDelete(int $id): bool
    {
        return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s'), 'estado' => 'baja']);
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        return $this->update($id, ['estado' => $estado]);
    }

    /**
     * Clientes activos sin pago en el período (para suspensión automática).
     */
    public function activosSinPago(int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id FROM clientes c
             WHERE c.estado = "activo" AND c.deleted_at IS NULL
               AND c.id NOT IN (
                   SELECT cliente_id FROM pagos
                   WHERE periodo_año = ? AND periodo_mes = ?
               )'
        );
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll();
    }

    public function nextNumeroInterno(): int
    {
        $stmt = $this->db->query('SELECT MAX(numero_interno) FROM clientes');
        return ((int)$stmt->fetchColumn()) + 1;
    }

    public function conteoEstados(): array
    {
        $stmt = $this->db->query(
            'SELECT estado, COUNT(*) AS total FROM clientes
             WHERE deleted_at IS NULL GROUP BY estado'
        );
        $result = ['activo' => 0, 'suspendido' => 0, 'baja' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['estado']] = (int)$row['total'];
        }
        return $result;
    }
}
