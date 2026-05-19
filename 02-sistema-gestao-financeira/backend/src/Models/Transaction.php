<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo de Transação
 */
class Transaction extends Model
{
    protected string $table = 'transactions';
    
    protected array $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'payment_method',
        'reference_number',
        'location',
        'tags',
        'notes',
        'attachment_path',
        'is_recurring',
        'recurring_frequency',
        'recurring_end_date',
        'parent_transaction_id',
        'status'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'account_id' => 'int',
        'category_id' => 'int',
        'amount' => 'float',
        'transaction_date' => 'date',
        'is_recurring' => 'bool',
        'recurring_end_date' => 'date',
        'parent_transaction_id' => 'int',
        'tags' => 'json'
    ];

    // Tipos de transação
    const TYPES = [
        'income' => 'Receita',
        'expense' => 'Despesa',
        'transfer' => 'Transferência'
    ];

    // Status da transação
    const STATUS = [
        'pending' => 'Pendente',
        'completed' => 'Concluída',
        'cancelled' => 'Cancelada'
    ];

    // Métodos de pagamento
    const PAYMENT_METHODS = [
        'cash' => 'Dinheiro',
        'debit_card' => 'Cartão de Débito',
        'credit_card' => 'Cartão de Crédito',
        'bank_transfer' => 'Transferência Bancária',
        'pix' => 'PIX',
        'check' => 'Cheque',
        'other' => 'Outros'
    ];

    /**
     * Buscar transações do usuário
     */
    public function getUserTransactions(int $userId, array $filters = [], int $page = 1, int $limit = 20): array
    {
        $conditions = ['user_id' => $userId];
        $sql = "SELECT t.*, a.name as account_name, c.name as category_name, c.color as category_color
                FROM {$this->table} t
                LEFT JOIN accounts a ON t.account_id = a.id
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = :user_id";
        
        // Aplicar filtros
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $conditions['type'] = $filters['type'];
        }
        
        if (!empty($filters['account_id'])) {
            $sql .= " AND t.account_id = :account_id";
            $conditions['account_id'] = $filters['account_id'];
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = :category_id";
            $conditions['category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.transaction_date >= :date_from";
            $conditions['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.transaction_date <= :date_to";
            $conditions['date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['amount_min'])) {
            $sql .= " AND t.amount >= :amount_min";
            $conditions['amount_min'] = $filters['amount_min'];
        }
        
        if (!empty($filters['amount_max'])) {
            $sql .= " AND t.amount <= :amount_max";
            $conditions['amount_max'] = $filters['amount_max'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (t.description LIKE :search OR t.notes LIKE :search)";
            $conditions['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $conditions['status'] = $filters['status'];
        }
        
        // Contar total
        $countSql = str_replace(
            'SELECT t.*, a.name as account_name, c.name as category_name, c.color as category_color',
            'SELECT COUNT(*) as total',
            $sql
        );
        $total = $this->db->fetch($countSql, $conditions)['total'];
        
        // Ordenação e paginação
        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";
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

    /**
     * Criar transação e atualizar saldo da conta
     */
    public function createTransaction(array $data): int
    {
        $this->beginTransaction();
        
        try {
            // Criar transação
            $transactionId = $this->create($data);
            
            // Atualizar saldo da conta se a transação estiver concluída
            if (($data['status'] ?? 'completed') === 'completed') {
                $accountModel = new Account();
                
                if ($data['type'] === 'income') {
                    $accountModel->updateBalance($data['account_id'], $data['amount'], 'add');
                } elseif ($data['type'] === 'expense') {
                    $accountModel->updateBalance($data['account_id'], $data['amount'], 'subtract');
                }
            }
            
            $this->commit();
            return $transactionId;
            
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Atualizar transação e ajustar saldo
     */
    public function updateTransaction(int $id, array $data): bool
    {
        $oldTransaction = $this->find($id);
        if (!$oldTransaction) {
            return false;
        }
        
        $this->beginTransaction();
        
        try {
            // Reverter o efeito da transação anterior no saldo
            if ($oldTransaction['status'] === 'completed') {
                $accountModel = new Account();
                
                if ($oldTransaction['type'] === 'income') {
                    $accountModel->updateBalance($oldTransaction['account_id'], $oldTransaction['amount'], 'subtract');
                } elseif ($oldTransaction['type'] === 'expense') {
                    $accountModel->updateBalance($oldTransaction['account_id'], $oldTransaction['amount'], 'add');
                }
            }
            
            // Atualizar transação
            $this->update($id, $data);
            
            // Aplicar novo efeito no saldo
            if (($data['status'] ?? $oldTransaction['status']) === 'completed') {
                $accountModel = new Account();
                $newType = $data['type'] ?? $oldTransaction['type'];
                $newAmount = $data['amount'] ?? $oldTransaction['amount'];
                $newAccountId = $data['account_id'] ?? $oldTransaction['account_id'];
                
                if ($newType === 'income') {
                    $accountModel->updateBalance($newAccountId, $newAmount, 'add');
                } elseif ($newType === 'expense') {
                    $accountModel->updateBalance($newAccountId, $newAmount, 'subtract');
                }
            }
            
            $this->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Deletar transação e ajustar saldo
     */
    public function deleteTransaction(int $id): bool
    {
        $transaction = $this->find($id);
        if (!$transaction) {
            return false;
        }
        
        $this->beginTransaction();
        
        try {
            // Reverter efeito no saldo se a transação estava concluída
            if ($transaction['status'] === 'completed') {
                $accountModel = new Account();
                
                if ($transaction['type'] === 'income') {
                    $accountModel->updateBalance($transaction['account_id'], $transaction['amount'], 'subtract');
                } elseif ($transaction['type'] === 'expense') {
                    $accountModel->updateBalance($transaction['account_id'], $transaction['amount'], 'add');
                }
            }
            
            // Deletar transação
            $this->delete($id);
            
            $this->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Obter resumo financeiro
     */
    public function getFinancialSummary(int $userId, string $period = 'month'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT 
                    type,
                    SUM(amount) as total,
                    COUNT(*) as count
                FROM {$this->table} 
                WHERE user_id = :user_id 
                AND status = 'completed'
                AND {$dateCondition}
                GROUP BY type";
        
        $results = $this->db->fetchAll($sql, ['user_id' => $userId]);
        
        $summary = [
            'income' => ['total' => 0, 'count' => 0],
            'expense' => ['total' => 0, 'count' => 0],
            'balance' => 0
        ];
        
        foreach ($results as $result) {
            $summary[$result['type']] = [
                'total' => (float) $result['total'],
                'count' => (int) $result['count']
            ];
        }
        
        $summary['balance'] = $summary['income']['total'] - $summary['expense']['total'];
        
        return $summary;
    }

    /**
     * Obter gastos por categoria
     */
    public function getExpensesByCategory(int $userId, string $period = 'month'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT 
                    c.name as category_name,
                    c.color as category_color,
                    SUM(t.amount) as total,
                    COUNT(t.id) as count
                FROM {$this->table} t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = :user_id 
                AND t.type = 'expense'
                AND t.status = 'completed'
                AND {$dateCondition}
                GROUP BY t.category_id, c.name, c.color
                ORDER BY total DESC";
        
        $results = $this->db->fetchAll($sql, ['user_id' => $userId]);
        
        return array_map(function($item) {
            return [
                'category' => $item['category_name'] ?? 'Sem Categoria',
                'color' => $item['category_color'] ?? '#666666',
                'total' => (float) $item['total'],
                'count' => (int) $item['count']
            ];
        }, $results);
    }

    /**
     * Obter evolução mensal
     */
    public function getMonthlyEvolution(int $userId, int $months = 12): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(transaction_date, '%Y-%m') as month,
                    type,
                    SUM(amount) as total
                FROM {$this->table}
                WHERE user_id = :user_id 
                AND status = 'completed'
                AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY month, type
                ORDER BY month ASC";
        
        $results = $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'months' => $months
        ]);
        
        $evolution = [];
        foreach ($results as $result) {
            $month = $result['month'];
            if (!isset($evolution[$month])) {
                $evolution[$month] = [
                    'month' => $month,
                    'income' => 0,
                    'expense' => 0,
                    'balance' => 0
                ];
            }
            
            $evolution[$month][$result['type']] = (float) $result['total'];
        }
        
        // Calcular saldo para cada mês
        foreach ($evolution as &$month) {
            $month['balance'] = $month['income'] - $month['expense'];
        }
        
        return array_values($evolution);
    }

    /**
     * Gerar condição de data baseada no período
     */
    private function getDateCondition(string $period): string
    {
        switch ($period) {
            case 'today':
                return "DATE(transaction_date) = CURDATE()";
            case 'week':
                return "transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            case 'month':
                return "MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE())";
            case 'quarter':
                return "transaction_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            case 'year':
                return "YEAR(transaction_date) = YEAR(CURDATE())";
            default:
                return "1=1";
        }
    }

    /**
     * Validar dados da transação
     */
    public function validateTransactionData(array $data): array
    {
        $errors = [];
        
        if (empty($data['type']) || !array_key_exists($data['type'], self::TYPES)) {
            $errors[] = 'Tipo de transação inválido';
        }
        
        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Valor deve ser um número positivo';
        }
        
        if (empty($data['description'])) {
            $errors[] = 'Descrição é obrigatória';
        }
        
        if (empty($data['account_id'])) {
            $errors[] = 'Conta é obrigatória';
        }
        
        if (empty($data['transaction_date'])) {
            $errors[] = 'Data da transação é obrigatória';
        }
        
        return $errors;
    }
}