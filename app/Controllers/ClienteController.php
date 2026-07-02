<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Pago;
use App\Models\HistorialEstado;
use App\Models\LogActividad;
use App\Services\SuspensionService;

class ClienteController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('clientes.ver');

        // La suspensión se verifica al cargar la lista (reemplaza el cron en local)
        (new SuspensionService())->run();

        $year  = (int)($request->get('year',  date('Y')));
        $month = (int)($request->get('month', date('m')));

        // Clamp para meses válidos
        $year  = max(2020, min(2099, $year));
        $month = max(1,    min(12,   $month));

        $clientes = (new Cliente())->listaPorPeriodo($year, $month);

        $this->render('clientes/index', [
            'clientes' => $clientes,
            'year'     => $year,
            'month'    => $month,
        ]);
    }

    public function show(Request $request, int $id): void
    {
        $this->requirePermission('clientes.ver');

        $cliente  = (new Cliente())->getById($id);
        if (!$cliente) {
            $this->abort(404, 'Cliente no encontrado.');
        }

        $year    = (int)($request->get('year',  date('Y')));
        $month   = (int)($request->get('month', date('m')));
        $historial = (new Pago())->historialCliente($id, 24);

        if ($request->isAjax()) {
            $this->json([
                'cliente'  => $cliente,
                'historial' => $historial,
            ]);
        }

        $this->render('clientes/show', [
            'cliente'  => $cliente,
            'historial' => $historial,
            'year'     => $year,
            'month'    => $month,
        ]);
    }

    public function store(Request $request): void
    {
        $this->requirePermission('clientes.crear');

        $v = Validator::make($request->all(), [
            'nombre'         => 'required|max:80',
            'apellido'       => 'required|max:80',
            'plan_id'        => 'required|integer',
            'precio_mensual' => 'required|positive',
            'fecha_alta'     => 'required|date',
        ]);

        if ($v->fails()) {
            $this->json(['error' => $v->firstError()], 422);
        }

        $data = [
            'nombre'          => trim($request->post('nombre', '')),
            'apellido'        => trim($request->post('apellido', '')),
            'dni'             => trim($request->post('dni', '')),
            'direccion'       => trim($request->post('direccion', '')),
            'barrio'          => trim($request->post('barrio', '')),
            'telefono'        => trim($request->post('telefono', '')),
            'whatsapp'        => trim($request->post('whatsapp', '')),
            'email'           => trim($request->post('email', '')),
            'plan_id'         => (int)$request->post('plan_id', 0),
            'precio_mensual'  => (float)$request->post('precio_mensual', 0),
            'fecha_alta'      => $request->post('fecha_alta', date('Y-m-d')),
            'estado'          => 'activo',
            'observaciones'   => trim($request->post('observaciones', '')),
        ];

        $id = (new Cliente())->create($data);
        (new LogActividad())->registrar('crear_cliente', 'Cliente', $id);

        $cliente = (new Cliente())->getById($id);
        $this->json(['success' => true, 'cliente' => $cliente]);
    }

    public function update(Request $request, int $id): void
    {
        $this->requirePermission('clientes.editar');

        $cliente = (new Cliente())->getById($id);
        if (!$cliente) {
            $this->json(['error' => 'Cliente no encontrado.'], 404);
        }

        $v = Validator::make($request->all(), [
            'nombre'         => 'required|max:80',
            'apellido'       => 'required|max:80',
            'precio_mensual' => 'required|positive',
        ]);

        if ($v->fails()) {
            $this->json(['error' => $v->firstError()], 422);
        }

        $data = [
            'nombre'         => trim($request->post('nombre', '')),
            'apellido'       => trim($request->post('apellido', '')),
            'dni'            => trim($request->post('dni', '')),
            'direccion'      => trim($request->post('direccion', '')),
            'barrio'         => trim($request->post('barrio', '')),
            'telefono'       => trim($request->post('telefono', '')),
            'whatsapp'       => trim($request->post('whatsapp', '')),
            'email'          => trim($request->post('email', '')),
            'plan_id'        => (int)$request->post('plan_id', $cliente['plan_id']),
            'precio_mensual' => (float)$request->post('precio_mensual', $cliente['precio_mensual']),
            'observaciones'  => trim($request->post('observaciones', '')),
        ];

        (new Cliente())->updateCliente($id, $data);
        (new LogActividad())->registrar('editar_cliente', 'Cliente', $id);

        $this->json(['success' => true, 'cliente' => (new Cliente())->getById($id)]);
    }

    public function destroy(Request $request, int $id): void
    {
        $this->requirePermission('clientes.eliminar');

        $cliente = (new Cliente())->getById($id);
        if (!$cliente) {
            $this->json(['error' => 'Cliente no encontrado.'], 404);
        }

        (new Cliente())->softDelete($id);
        (new HistorialEstado())->registrar(
            $id, $cliente['estado'], 'baja', 'Baja manual', \App\Core\Auth::id()
        );
        (new LogActividad())->registrar('dar_baja_cliente', 'Cliente', $id);

        $this->json(['success' => true]);
    }
}
