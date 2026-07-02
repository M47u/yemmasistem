<?php

namespace App\Models;

use App\Core\Model;

class Plan extends Model
{
    protected string $table = 'planes';

    public function allActivos(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM planes WHERE activo = 1 ORDER BY velocidad_mb ASC'
        );
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        return $this->find($id);
    }

    public function create(array $data): int
    {
        return $this->insert($data);
    }

    public function updatePlan(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }
}
