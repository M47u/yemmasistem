<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Cliente;
use App\Models\HistorialEstado;

class SuspensionService
{
    private Cliente $clientes;
    private HistorialEstado $historial;

    public function __construct()
    {
        $this->clientes  = new Cliente();
        $this->historial = new HistorialEstado();
    }

    /**
     * Ejecuta la lógica de suspensión automática.
     * Se llama una vez por sesión; guarda la última ejecución en configuracion.
     */
    public function run(): void
    {
        $hoy = (int)date('j');
        $pdo  = Database::pdo();

        // Obtener día de vencimiento configurado (default 11)
        $stmt = $pdo->prepare('SELECT valor FROM configuracion WHERE clave = "dia_vencimiento"');
        $stmt->execute();
        $diaVencimiento = (int)($stmt->fetchColumn() ?: 11);

        if ($hoy < $diaVencimiento) {
            return;
        }

        // Evitar ejecutar más de una vez por día
        $stmt = $pdo->prepare('SELECT valor FROM configuracion WHERE clave = "ultima_suspension"');
        $stmt->execute();
        $ultimaEjecucion = $stmt->fetchColumn();

        if ($ultimaEjecucion === date('Y-m-d')) {
            return;
        }

        $year  = (int)date('Y');
        $month = (int)date('m');

        $clientesSinPago = $this->clientes->activosSinPago($year, $month);

        foreach ($clientesSinPago as $cliente) {
            $this->clientes->cambiarEstado((int)$cliente['id'], 'suspendido');
            $this->historial->registrar(
                (int)$cliente['id'],
                'activo',
                'suspendido',
                'Suspensión automática por vencimiento (día ' . $diaVencimiento . ')',
                null
            );
        }

        // Registrar la fecha de última ejecución
        $pdo->prepare(
            'INSERT INTO configuracion (clave, valor) VALUES ("ultima_suspension", ?)
             ON DUPLICATE KEY UPDATE valor = ?'
        )->execute([date('Y-m-d'), date('Y-m-d')]);
    }
}
