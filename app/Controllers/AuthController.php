<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\LogActividad;

class AuthController extends Controller
{
    public function loginForm(Request $request): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->render('auth/login', [], 'auth');
    }

    public function login(Request $request): void
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:4',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->firstError());
            $this->redirect('/login');
        }

        $email    = trim($request->post('email', ''));
        $password = $request->post('password', '');

        if (!Auth::attempt($email, $password)) {
            $this->flash('error', 'Email o contraseña incorrectos.');
            $this->redirect('/login');
        }

        (new LogActividad())->registrar('login', 'Usuario');
        $this->redirect('/');
    }

    public function logout(Request $request): void
    {
        (new LogActividad())->registrar('logout', 'Usuario');
        Auth::logout();
        $this->redirect('/login');
    }
}
