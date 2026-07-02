<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Core\Database;
use App\Models\Usuario;
use App\Models\LogActividad;

class UsuarioController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('usuarios.ver');
        $usuarios = (new Usuario())->allWithRoles();
        $roles    = Database::pdo()->query('SELECT * FROM roles ORDER BY id')->fetchAll();
        $this->render('usuarios/index', compact('usuarios', 'roles'));
    }

    public function store(Request $request): void
    {
        $this->requirePermission('usuarios.crear');

        $v = Validator::make($request->all(), [
            'nombre'   => 'required|max:60',
            'apellido' => 'required|max:60',
            'email'    => 'required|email|max:120',
            'password' => 'required|min:6',
            'rol_id'   => 'required|integer',
        ]);

        if ($v->fails()) {
            $this->json(['error' => $v->firstError()], 422);
        }

        $id = (new Usuario())->create([
            'rol_id'   => (int)$request->post('rol_id'),
            'nombre'   => trim($request->post('nombre', '')),
            'apellido' => trim($request->post('apellido', '')),
            'email'    => strtolower(trim($request->post('email', ''))),
            'password' => $request->post('password', ''),
            'activo'   => 1,
        ]);

        (new LogActividad())->registrar('crear_usuario', 'Usuario', $id);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function update(Request $request, int $id): void
    {
        $this->requirePermission('usuarios.editar');

        $data = [
            'nombre'   => trim($request->post('nombre', '')),
            'apellido' => trim($request->post('apellido', '')),
            'rol_id'   => (int)$request->post('rol_id', 0),
            'activo'   => (int)$request->post('activo', 1),
        ];

        $password = $request->post('password', '');
        if ($password) {
            (new Usuario())->updatePassword($id, $password);
        }

        (new Usuario())->updateProfile($id, $data);
        (new LogActividad())->registrar('editar_usuario', 'Usuario', $id);
        $this->json(['success' => true]);
    }
}
