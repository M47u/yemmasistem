<?php

namespace App\Models;

use App\Core\Model;

class Pago extends Model
{
    protected string $table = 'pagos';

    public function registrar(array $data): int
    {
        return $this->insert($data);
    }

    public function existePago(int $clienteId, int $year, int $month): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM pagos WHERE cliente_id = ? AND periodo_año = ? AND periodo_mes = ? LIMIT 1'
        );
        $stmt->execute([$clienteId, $year, $month]);
        return (bool)$stmt->fetch();
    }

    public function getPago(int $clienteId, int $year, int $month): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, mp.nombre AS metodo_nombre, u.nombre AS cajero_nombre, u.apellido AS cajero_apellido
             FROM pagos p
             LEFT JOIN metodos_pago mp ON mp.id = p.metodo_pago_id
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.cliente_id = ? AND p.periodo_año = ? AND p.periodo_mes = ? LIMIT 1'
        );
        $stmt->execute([$clienteId, $year, $month]);
        return $stmt->fetch() ?: null;
    }

    public function historialCliente(int $clienteId, int $limit = 24): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, mp.nombre AS metodo_nombre, u.nombre AS cajero_nombre
             FROM pagos p
             LEFT JOIN metodos_pago mp ON mp.id = p.metodo_pago_id
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.cliente_id = ?
             ORDER BY p.periodo_año DESC, p.periodo_mes DESC
             LIMIT ?'
        );
        $stmt->execute([$clienteId, $limit]);
        return $stmt->fetchAll();
    }

    public function eliminar(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Total cobrado en un período específico.
     */
    public function totalPeriodo(int $year, int $month): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(importe), 0) FROM pagos WHERE periodo_año = ? AND periodo_mes = ?'
        );
        $stmt->execute([$year, $month]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Total cobrado hoy.
     */
    public function totalHoy(): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(importe), 0) FROM pagos WHERE fecha_pago = CURDATE()'
        );
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    /**
     * Conteo de pagos del día.
     */
    public function conteoHoy(): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM pagos WHERE fecha_pago = CURDATE()'
        );
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Pagos del día con detalle.
     */
    public function pagosHoy(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*, c.nombre, c.apellido, mp.nombre AS metodo_nombre
             FROM pagos p
             JOIN clientes c ON c.id = p.cliente_id
             LEFT JOIN metodos_pago mp ON mp.id = p.metodo_pago_id
             WHERE p.fecha_pago = CURDATE()
             ORDER BY p.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Cantidad de clientes que pagaron en un período.
     */
    public function conteoPagados(int $year, int $month): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM pagos WHERE periodo_año = ? AND periodo_mes = ?'
        );
        $stmt->execute([$year, $month]);
        return (int)$stmt->fetchColumn();
    }
}
