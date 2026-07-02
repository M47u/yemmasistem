<?php

namespace App\Services;

use App\Core\Auth;
use App\Models\Pago;
use App\Models\Cliente;
use App\Models\HistorialEstado;
use App\Models\LogActividad;

class PagoService
{
    private Pago $pagos;
    private Cliente $clientes;
    private HistorialEstado $historial;
    private LogActividad $log;

    public function __construct()
    {
        $this->pagos    = new Pago();
        $this->clientes = new Cliente();
        $this->historial = new HistorialEstado();
        $this->log      = new LogActividad();
    }

    /**
     * Registra un pago y reactiva al cliente si estaba suspendido.
     * Retorna el pago recién creado.
     */
    public function registrar(
        int $clienteId,
        int $year,
        int $month,
        float $importe,
        int $metodoPagoId,
        ?string $observaciones = null
    ): array {
        if ($this->pagos->existePago($clienteId, $year, $month)) {
            throw new \RuntimeException('El cliente ya tiene registrado el pago de ese período.');
        }

        $cliente = $this->clientes->getById($clienteId);
        if (!$cliente) {
            throw new \RuntimeException('Cliente no encontrado.');
        }

        $pagoId = $this->pagos->registrar([
            'cliente_id'    => $clienteId,
            'periodo_año'   => $year,
            'periodo_mes'   => $month,
            'fecha_pago'    => date('Y-m-d'),
            'importe'       => $importe,
            'metodo_pago_id' => $metodoPagoId,
            'usuario_id'    => Auth::id(),
            'observaciones' => $observaciones,
        ]);

        // Reactivar si estaba suspendido
        if ($cliente['estado'] === 'suspendido') {
            $this->clientes->cambiarEstado($clienteId, 'activo');
            $this->historial->registrar(
                $clienteId,
                'suspendido',
                'activo',
                'Reactivación automática por registro de pago',
                Auth::id()
            );
        }

        $this->log->registrar('registrar_pago', 'Pago', $pagoId, [
            'cliente_id' => $clienteId,
            'periodo'    => "$year-$month",
            'importe'    => $importe,
        ]);

        return $this->pagos->getPago($clienteId, $year, $month);
    }

    /**
     * Toggle rápido para la pantalla principal.
     * Si existe el pago del período: lo elimina. Si no: lo registra con el precio del plan.
     */
    public function toggle(int $clienteId, int $year, int $month): array
    {
        $cliente = $this->clientes->getById($clienteId);
        if (!$cliente) {
            throw new \RuntimeException('Cliente no encontrado.');
        }

        $existePago = $this->pagos->getPago($clienteId, $year, $month);

        if ($existePago) {
            // Eliminar el pago (anulación rápida)
            $this->pagos->eliminar((int)$existePago['id']);

            // Si el cliente está activo y el mes es el actual, marcar como suspendido
            // (solo si estamos en el período de cobro)
            $hoy = (int)date('j');
            $esActual = $year === (int)date('Y') && $month === (int)date('m');
            if ($esActual && $hoy >= 11 && $cliente['estado'] === 'activo') {
                $this->clientes->cambiarEstado($clienteId, 'suspendido');
                $this->historial->registrar($clienteId, 'activo', 'suspendido', 'Pago anulado manualmente', Auth::id());
            }

            return ['pagado' => false, 'pago' => null];
        }

        // Registrar pago con efectivo por defecto y precio del plan
        $pagoData = $this->registrar(
            $clienteId, $year, $month,
            (float)$cliente['precio_mensual'],
            1, // Efectivo por defecto
            null
        );

        return ['pagado' => true, 'pago' => $pagoData];
    }
}
