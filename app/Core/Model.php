<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    protected function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    protected function findAll(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir  = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY $orderBy $dir");
        return $stmt->fetchAll();
    }

    protected function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)"
        );
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    protected function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET $sets WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([...array_values($data), $id]);
    }

    protected function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([$id]);
    }

    protected function count(string $where = '', array $params = []): int
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table}" . ($where ? " WHERE $where" : '');
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
