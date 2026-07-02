<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Pago;

class DashboardService
{
    private Cliente $clientes;
    private Pago $pagos;

    public function __construct()
    {
        $this->clientes = new Cliente();
        $this->pagos    = new Pago();
    }

    public function metricas(): array
    {
        $year  = (int)date('Y');
        $month = (int)date('m');

        $estados    = $this->clientes->conteoEstados();
        $totalClientes = array_sum($estados);

        return [
            'clientes_activos'    => $estados['activo']     ?? 0,
            'clientes_suspendidos' => $estados['suspendido'] ?? 0,
            'clientes_baja'       => $estados['baja']        ?? 0,
            'total_clientes'      => $totalClientes,
            'pagos_hoy'           => $this->pagos->conteoHoy(),
            'ingresos_hoy'        => $this->pagos->totalHoy(),
            'pagos_mes'           => $this->pagos->conteoPagados($year, $month),
            'ingresos_mes'        => $this->pagos->totalPeriodo($year, $month),
            'year'                => $year,
            'month'               => $month,
        ];
    }
}
