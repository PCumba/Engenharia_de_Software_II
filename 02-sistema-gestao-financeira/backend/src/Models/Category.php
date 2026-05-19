<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo de Categoria
 */
class Category extends Model
{
    protected string $table = 'categories';

    protected array $fillable = [
        'user_id', 'name', 'type', 'color', 'icon',
        'parent_id', 'is_active', 'description'
    ];

    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'parent_id' => 'int',
        'is_active' => 'bool'
    ];

    /**
     * Buscar categorias do utilizador (inclui as globais user_id=0)
     */
    public function getUserCategories(int $userId, ?string $type = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE (user_id = :user_id OR user_id = 0) AND is_active = 1";
        $params = ['user_id' => $userId];

        if ($type) {
            $sql .= " AND (type = :type OR type = 'both')";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY name ASC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Buscar categorias filhas
     */
    public function getChildren(int $parentId): array
    {
        return $this->where(['parent_id' => $parentId, 'is_active' => true]);
    }

    /**
     * Verificar se nome já existe para o utilizador
     */
    public function nameExists(int $userId, string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = :user_id AND name = :name";
        $params = ['user_id' => $userId, 'name' => $name];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'] > 0;
    }
}
