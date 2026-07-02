<?php

namespace App\Core;

class Auth
{
    private const SESSION_KEY = '_auth_user';

    public static function attempt(string $email, string $password): bool
    {
        $pdo  = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT u.*, r.nombre AS rol_nombre
             FROM usuarios u
             JOIN roles r ON r.id = u.rol_id
             WHERE u.email = ? AND u.activo = 1
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Actualiza último login
        $pdo->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?')
            ->execute([$user['id']]);

        Session::regenerate();
        Session::set(self::SESSION_KEY, [
            'id'       => $user['id'],
            'nombre'   => $user['nombre'],
            'apellido' => $user['apellido'],
            'email'    => $user['email'],
            'rol'      => $user['rol_nombre'],
            'rol_id'   => $user['rol_id'],
        ]);

        return true;
    }

    public static function user(): ?array
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function id(): ?int
    {
        return Session::get(self::SESSION_KEY)['id'] ?? null;
    }

    public static function rol(): string
    {
        return Session::get(self::SESSION_KEY)['rol'] ?? '';
    }

    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function can(string $permission): bool
    {
        $permissions = require CONFIG_PATH . '/permissions.php';
        $rol         = self::rol();
        return in_array($permission, $permissions[$rol] ?? [], true);
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
