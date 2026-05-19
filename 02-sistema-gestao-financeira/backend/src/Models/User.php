<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo de Usuário
 */
class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'timezone',
        'currency',
        'language',
        'email_verified_at',
        'is_active'
    ];
    
    protected array $hidden = [
        'password',
        'remember_token'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'is_active' => 'bool',
        'email_verified_at' => 'datetime'
    ];

    /**
     * Buscar usuário por email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->first(['email' => $email]);
    }

    /**
     * Verificar se email já existe
     */
    public function emailExists(string $email, int $excludeId = null): bool
    {
        $conditions = ['email' => $email];
        
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE email = :email AND id != :exclude_id";
            $result = $this->db->fetch($sql, [
                'email' => $email,
                'exclude_id' => $excludeId
            ]);
        } else {
            $result = $this->db->fetch(
                "SELECT COUNT(*) as total FROM {$this->table} WHERE email = :email",
                ['email' => $email]
            );
        }
        
        return $result['total'] > 0;
    }

    /**
     * Criar hash da senha
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verificar senha
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Atualizar último login
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->db->update(
            $this->table,
            ['last_login_at' => date('Y-m-d H:i:s')],
            ['id' => $userId]
        ) > 0;
    }

    /**
     * Ativar usuário
     */
    public function activate(int $userId): bool
    {
        return $this->update($userId, [
            'is_active' => true,
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Desativar usuário
     */
    public function deactivate(int $userId): bool
    {
        return $this->update($userId, ['is_active' => false]);
    }

    /**
     * Buscar usuários ativos
     */
    public function getActiveUsers(): array
    {
        return $this->where(['is_active' => true]);
    }

    /**
     * Estatísticas de usuários
     */
    public function getStats(): array
    {
        $total = $this->count();
        $active = $this->count(['is_active' => true]);
        $verified = $this->db->fetch(
            "SELECT COUNT(*) as total FROM {$this->table} 
             WHERE email_verified_at IS NOT NULL"
        )['total'];
        
        $recentRegistrations = $this->db->fetch(
            "SELECT COUNT(*) as total FROM {$this->table} 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )['total'];

        return [
            'total_users' => $total,
            'active_users' => $active,
            'verified_users' => $verified,
            'recent_registrations' => $recentRegistrations,
            'verification_rate' => $total > 0 ? round(($verified / $total) * 100, 2) : 0
        ];
    }

    /**
     * Buscar usuários por filtros
     */
    public function search(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $conditions = [];
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        
        if (!empty($filters['name'])) {
            $sql .= " AND name LIKE :name";
            $conditions['name'] = '%' . $filters['name'] . '%';
        }
        
        if (!empty($filters['email'])) {
            $sql .= " AND email LIKE :email";
            $conditions['email'] = '%' . $filters['email'] . '%';
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = :is_active";
            $conditions['is_active'] = $filters['is_active'];
        }
        
        if (!empty($filters['created_from'])) {
            $sql .= " AND created_at >= :created_from";
            $conditions['created_from'] = $filters['created_from'];
        }
        
        if (!empty($filters['created_to'])) {
            $sql .= " AND created_at <= :created_to";
            $conditions['created_to'] = $filters['created_to'];
        }
        
        // Contar total
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);
        $total = $this->db->fetch($countSql, $conditions)['total'];
        
        // Adicionar ordenação e paginação
        $sql .= " ORDER BY created_at DESC";
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
        
        $results = $this->db->fetchAll($sql, $conditions);
        
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
}