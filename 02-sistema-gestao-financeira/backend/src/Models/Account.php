<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo de Conta Bancária
 */
class Account extends Model
{
    protected string $table = 'accounts';
    
    protected array $fillable = [
        'user_id',
        'name',
        'type',
        'bank_name',
        'account_number',
        'initial_balance',
        'current_balance',
        'currency',
        'color',
        'icon',
        'is_active',
        'description'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'initial_balance' => 'float',
        'current_balance' => 'float',
        'is_active' => 'bool'
    ];

    // Tipos de conta disponíveis
    const TYPES = [
        'checking' => 'Conta Corrente',
        'savings' => 'Poupança',
        'credit_card' => 'Cartão de Crédito',
        'investment' => 'Investimento',
        'cash' => 'Dinheiro',
        'other' => 'Outros'
    ];

    /**
     * Buscar contas do usuário
     */
    public function getUserAccounts(int $userId, bool $activeOnly = true): array
    {
        $conditions = ['user_id' => $userId];
        
        if ($activeOnly) {
            $conditions['is_active'] = true;
        }
        
        return $this->where($conditions);
    }

    /**
     * Calcular saldo total do usuário
     */
    public function getTotalBalance(int $userId, string $currency = 'BRL'): float
    {
        $sql = "SELECT SUM(current_balance) as total 
                FROM {$this->table} 
                WHERE user_id = :user_id 
                AND is_active = 1 
                AND currency = :currency";
        
        $result = $this->db->fetch($sql, [
            'user_id' => $userId,
            'currency' => $currency
        ]);
        
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Atualizar saldo da conta
     */
    public function updateBalance(int $accountId, float $amount, string $operation = 'add'): bool
    {
        $account = $this->find($accountId);
        if (!$account) {
            return false;
        }
        
        $newBalance = $operation === 'add' 
            ? $account['current_balance'] + $amount
            : $account['current_balance'] - $amount;
        
        return $this->update($accountId, ['current_balance' => $newBalance]);
    }

    /**
     * Transferir entre contas
     */
    public function transfer(int $fromAccountId, int $toAccountId, float $amount): bool
    {
        $this->beginTransaction();
        
        try {
            // Debitar da conta origem
            if (!$this->updateBalance($fromAccountId, $amount, 'subtract')) {
                throw new \Exception('Erro ao debitar da conta origem');
            }
            
            // Creditar na conta destino
            if (!$this->updateBalance($toAccountId, $amount, 'add')) {
                throw new \Exception('Erro ao creditar na conta destino');
            }
            
            $this->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Verificar se conta pertence ao usuário
     */
    public function belongsToUser(int $accountId, int $userId): bool
    {
        $account = $this->find($accountId);
        return $account && $account['user_id'] === $userId;
    }

    /**
     * Obter estatísticas das contas
     */
    public function getAccountStats(int $userId): array
    {
        $accounts = $this->getUserAccounts($userId);
        
        $stats = [
            'total_accounts' => count($accounts),
            'active_accounts' => 0,
            'total_balance' => 0,
            'by_type' => [],
            'by_currency' => []
        ];
        
        foreach ($accounts as $account) {
            if ($account['is_active']) {
                $stats['active_accounts']++;
            }
            
            $stats['total_balance'] += $account['current_balance'];
            
            // Agrupar por tipo
            $type = $account['type'];
            if (!isset($stats['by_type'][$type])) {
                $stats['by_type'][$type] = [
                    'count' => 0,
                    'balance' => 0
                ];
            }
            $stats['by_type'][$type]['count']++;
            $stats['by_type'][$type]['balance'] += $account['current_balance'];
            
            // Agrupar por moeda
            $currency = $account['currency'];
            if (!isset($stats['by_currency'][$currency])) {
                $stats['by_currency'][$currency] = [
                    'count' => 0,
                    'balance' => 0
                ];
            }
            $stats['by_currency'][$currency]['count']++;
            $stats['by_currency'][$currency]['balance'] += $account['current_balance'];
        }
        
        return $stats;
    }

    /**
     * Buscar contas com saldo baixo
     */
    public function getLowBalanceAccounts(int $userId, float $threshold = 100): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND is_active = 1 
                AND current_balance < :threshold 
                AND type != 'credit_card'
                ORDER BY current_balance ASC";
        
        $results = $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'threshold' => $threshold
        ]);
        
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Histórico de saldos (para gráficos)
     */
    public function getBalanceHistory(int $accountId, int $days = 30): array
    {
        // Esta função requer uma tabela de histórico de saldos
        // Por simplicidade, vamos retornar dados simulados
        $account = $this->find($accountId);
        if (!$account) {
            return [];
        }
        
        $history = [];
        $currentBalance = $account['current_balance'];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            // Simular variação de saldo (em produção, buscar da tabela de histórico)
            $variation = rand(-100, 100);
            $balance = $currentBalance + $variation;
            
            $history[] = [
                'date' => $date,
                'balance' => $balance
            ];
        }
        
        return $history;
    }

    /**
     * Validar dados da conta
     */
    public function validateAccountData(array $data): array
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Nome da conta é obrigatório';
        }
        
        if (empty($data['type']) || !array_key_exists($data['type'], self::TYPES)) {
            $errors[] = 'Tipo de conta inválido';
        }
        
        if (!isset($data['initial_balance']) || !is_numeric($data['initial_balance'])) {
            $errors[] = 'Saldo inicial deve ser um número válido';
        }
        
        if (empty($data['currency'])) {
            $errors[] = 'Moeda é obrigatória';
        }
        
        return $errors;
    }
}