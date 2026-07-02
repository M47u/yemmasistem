<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Pago;
use App\Services\PagoService;

class PagoController extends Controller
{
    public function toggle(Request $request): void
    {
        $this->requirePermission('pagos.registrar');

        $clienteId = (int)$request->post('cliente_id', 0);
        $year      = (int)$request->post('year',  date('Y'));
        $month     = (int)$request->post('month', date('m'));

        if (!$clienteId) {
            $this->json(['error' => 'Cliente requerido.'], 422);
        }

        try {
            $result = (new PagoService())->toggle($clienteId, $year, $month);
            $this->json(['success' => true, ...$result]);
        } catch (\RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request): void
    {
        $this->requirePermission('pagos.registrar');

        $v = Validator::make($request->all(), [
            'cliente_id'    => 'required|integer',
            'year'          => 'required|integer',
            'month'         => 'required|integer',
            'importe'       => 'required|positive',
            'metodo_pago_id' => 'required|integer',
        ]);

        if ($v->fails()) {
            $this->json(['error' => $v->firstError()], 422);
        }

        try {
            $pago = (new PagoService())->registrar(
                (int)$request->post('cliente_id'),
                (int)$request->post('year'),
                (int)$request->post('month'),
                (float)$request->post('importe'),
                (int)$request->post('metodo_pago_id'),
                $request->post('observaciones')
            );
            $this->json(['success' => true, 'pago' => $pago]);
        } catch (\RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function historial(Request $request, int $clienteId): void
    {
        $this->requirePermission('pagos.ver');

        $historial = (new Pago())->historialCliente($clienteId, 24);
        $this->json(['historial' => $historial]);
    }
}
