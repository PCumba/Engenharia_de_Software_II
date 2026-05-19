<?php

namespace App\Core;

/**
 * Classe base para modelos
 */
abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];
    protected bool $timestamps = true;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar todos os registros
     */
    public function all(array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table}";
        
        if (property_exists($this, 'softDeletes') && $this->softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $results = $this->db->fetchAll($sql);
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Buscar por ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        if (property_exists($this, 'softDeletes') && $this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $result = $this->db->fetch($sql, ['id' => $id]);
        
        if ($result) {
            return $this->castAttributes($result);
        }
        
        return null;
    }

    /**
     * Buscar com condições
     */
    public function where(array $conditions, array $columns = ['*']): array
    {
        $columnsStr = implode(', ', $columns);
        $whereClause = [];
        
        foreach (array_keys($conditions) as $column) {
            $whereClause[] = "{$column} = :{$column}";
        }
        
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
        
        if (property_exists($this, 'softDeletes') && $this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $results = $this->db->fetchAll($sql, $conditions);
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Buscar primeiro registro com condições
     */
    public function first(array $conditions): ?array
    {
        $results = $this->where($conditions);
        return $results[0] ?? null;
    }

    /**
     * Criar novo registro
     */
    public function create(array $data): int
    {
        // Filtrar apenas campos permitidos
        $filteredData = $this->filterFillable($data);
        
        // Adicionar timestamps se habilitado
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $filteredData['created_at'] = $now;
            $filteredData['updated_at'] = $now;
        }
        
        return $this->db->insert($this->table, $filteredData);
    }

    /**
     * Atualizar registro
     */
    public function update(int $id, array $data): bool
    {
        // Filtrar apenas campos permitidos
        $filteredData = $this->filterFillable($data);
        
        // Adicionar timestamp de atualização
        if ($this->timestamps) {
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $where = [$this->primaryKey => $id];
        
        if (property_exists($this, 'softDeletes') && $this->softDeletes) {
            $where['deleted_at'] = null;
        }
        
        return $this->db->update($this->table, $filteredData, $where) > 0;
    }

    /**
     * Deletar registro
     */
    public function delete(int $id): bool
    {
        if (property_exists($this, 'softDeletes') && $this->softDeletes) {
            // Soft delete
            return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            // Hard delete
            return $this->db->delete($this->table, [$this->primaryKey => $id]) > 0;
        }
    }

    /**
     * Contar registros
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach (array_keys($conditions) as $column) {
                $whereClause[] = "{$column} = :{$column}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if (property_exists($this, 'softDeletes') && $this->softDeletes) {
            $sql .= empty($conditions) ? " WHERE deleted_at IS NULL" : " AND deleted_at IS NULL";
        }
        
        $result = $this->db->fetch($sql, $conditions);
        return (int) $result['total'];
    }

    /**
     * Paginação
     */
    public function paginate(int $page = 1, int $limit = 20, array $conditions = []): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach (array_keys($conditions) as $column) {
                $whereClause[] = "{$column} = :{$column}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if (property_exists($this, 'softDeletes') && $this->softDeletes) {
            $sql .= empty($conditions) ? " WHERE deleted_at IS NULL" : " AND deleted_at IS NULL";
        }
        
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
        
        $results = $this->db->fetchAll($sql, $conditions);
        $total = $this->count($conditions);
        
        return [
            'data' => array_map([$this, 'castAttributes'], $results),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * Filtrar campos permitidos
     */
    private function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Aplicar casting aos atributos
     */
    private function castAttributes(array $attributes): array
    {
        foreach ($this->casts as $key => $type) {
            if (isset($attributes[$key])) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $attributes[$key] = (int) $attributes[$key];
                        break;
                    case 'float':
                    case 'double':
                        $attributes[$key] = (float) $attributes[$key];
                        break;
                    case 'bool':
                    case 'boolean':
                        $attributes[$key] = (bool) $attributes[$key];
                        break;
                    case 'array':
                    case 'json':
                        $attributes[$key] = json_decode($attributes[$key], true);
                        break;
                    case 'date':
                        $attributes[$key] = date('Y-m-d', strtotime($attributes[$key]));
                        break;
                    case 'datetime':
                        $attributes[$key] = date('Y-m-d H:i:s', strtotime($attributes[$key]));
                        break;
                }
            }
        }
        
        // Remover campos ocultos
        foreach ($this->hidden as $field) {
            unset($attributes[$field]);
        }
        
        return $attributes;
    }

    /**
     * Executar query customizada
     */
    public function query(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Iniciar transação
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirmar transação
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Reverter transação
     */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}