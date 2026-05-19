<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo de Orçamento
 */
class Budget extends Model
{
    protected string $table = 'budgets';

    protected array $fillable = [
        'user_id', 'category_id', 'name', 'amount', 'period',
        'start_date', 'end_date', 'alert_percentage', 'is_active', 'description'
    ];

    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'category_id' => 'int',
        'amount' => 'float',
        'alert_percentage' => 'float',
        'is_active' => 'bool'
    ];

    /**
     * Buscar orçamentos do utilizador com gasto atual
     */
    public function getUserBudgets(int $userId): array
    {
        $sql = "SELECT b.*, c.name as category_name, c.color as category_color,
                       COALESCE(SUM(t.amount), 0) as spent
                FROM {$this->table} b
                LEFT JOIN categories c ON b.category_id = c.id
                LEFT JOIN transactions t ON (
                    t.user_id = b.user_id
                    AND (b.category_id IS NULL OR t.category_id = b.category_id)
                    AND t.type = 'expense'
                    AND t.status = 'completed'
                    AND MONTH(t.transaction_date) = MONTH(CURDATE())
                    AND YEAR(t.transaction_date) = YEAR(CURDATE())
                )
                WHERE b.user_id = :user_id AND b.is_active = 1
                GROUP BY b.id
                ORDER BY b.created_at DESC";

        $results = $this->db->fetchAll($sql, ['user_id' => $userId]);

        return array_map(function ($budget) {
            $spent = (float) $budget['spent'];
            $amount = (float) $budget['amount'];
            $percentage = $amount > 0 ? ($spent / $amount) * 100 : 0;

            $status = 'ok';
            if ($percentage >= 100) $status = 'exceeded';
            elseif ($percentage >= $budget['alert_percentage']) $status = 'warning';

            return array_merge($budget, [
                'spent' => $spent,
                'remaining' => $amount - $spent,
                'percentage' => round($percentage, 2),
                'status' => $status
            ]);
        }, $results);
    }

    /**
     * Verificar status de um orçamento específico
     */
    public function getBudgetStatus(int $budgetId): ?array
    {
        $budget = $this->find($budgetId);
        if (!$budget) return null;

        $sql = "SELECT COALESCE(SUM(amount), 0) as spent
                FROM transactions
                WHERE user_id = :user_id
                AND category_id = :category_id
                AND type = 'expense'
                AND status = 'completed'
                AND MONTH(transaction_date) = MONTH(CURDATE())
                AND YEAR(transaction_date) = YEAR(CURDATE())";

        $result = $this->db->fetch($sql, [
            'user_id' => $budget['user_id'],
            'category_id' => $budget['category_id']
        ]);

        $spent = (float) ($result['spent'] ?? 0);
        $amount = (float) $budget['amount'];
        $percentage = $amount > 0 ? ($spent / $amount) * 100 : 0;

        return [
            'budget' => $budget,
            'spent' => $spent,
            'remaining' => $amount - $spent,
            'percentage' => round($percentage, 2),
            'status' => $percentage >= 100 ? 'exceeded' : ($percentage >= $budget['alert_percentage'] ? 'warning' : 'ok')
        ];
    }
}
