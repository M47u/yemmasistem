<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.nombre AS rol_nombre
             FROM usuarios u JOIN roles r ON r.id = u.rol_id
             WHERE u.email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findWithRole(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.nombre AS rol_nombre
             FROM usuarios u JOIN roles r ON r.id = u.rol_id
             WHERE u.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function allWithRoles(): array
    {
        $stmt = $this->db->query(
            'SELECT u.*, r.nombre AS rol_nombre
             FROM usuarios u JOIN roles r ON r.id = u.rol_id
             ORDER BY u.apellido, u.nombre'
        );
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);
        return $this->insert($data);
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, ['password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)]);
    }

    public function getById(int $id): ?array
    {
        return $this->find($id);
    }

    public function updateProfile(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function softDelete(int $id): bool
    {
        return $this->update($id, ['activo' => 0]);
    }
}
