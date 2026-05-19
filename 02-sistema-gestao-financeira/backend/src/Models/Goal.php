<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo de Meta Financeira
 */
class Goal extends Model
{
    protected string $table = 'goals';

    protected array $fillable = [
        'user_id', 'name', 'description', 'target_amount', 'current_amount',
        'target_date', 'category', 'priority', 'is_active', 'achieved_at'
    ];

    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'target_amount' => 'float',
        'current_amount' => 'float',
        'is_active' => 'bool'
    ];

    /**
     * Buscar metas do utilizador
     */
    public function getUserGoals(int $userId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        $params = ['user_id' => $userId];

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY priority DESC, target_date ASC";
        $results = $this->db->fetchAll($sql, $params);

        return array_map(function ($goal) {
            $goal['percentage'] = $goal['target_amount'] > 0
                ? min(round(($goal['current_amount'] / $goal['target_amount']) * 100, 2), 100)
                : 0;
            return $goal;
        }, $results);
    }

    /**
     * Atualizar progresso da meta
     */
    public function updateProgress(int $goalId, float $amount): bool
    {
        $goal = $this->find($goalId);
        if (!$goal) return false;

        $newAmount = (float) $goal['current_amount'] + $amount;
        $data = ['current_amount' => $newAmount];

        // Verificar se a meta foi atingida
        if ($newAmount >= (float) $goal['target_amount']) {
            $data['achieved_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($goalId, $data);
    }

    /**
     * Calcular projeção de conclusão
     */
    public function getProjection(int $goalId): ?array
    {
        $goal = $this->find($goalId);
        if (!$goal) return null;

        $remaining = (float) $goal['target_amount'] - (float) $goal['current_amount'];
        $daysSinceCreation = max(1, (time() - strtotime($goal['created_at'])) / 86400);
        $dailyRate = (float) $goal['current_amount'] / $daysSinceCreation;

        $projectedDays = $dailyRate > 0 ? ceil($remaining / $dailyRate) : null;
        $projectedDate = $projectedDays ? date('Y-m-d', strtotime("+{$projectedDays} days")) : null;

        return [
            'goal' => $goal,
            'remaining' => $remaining,
            'daily_rate' => round($dailyRate, 2),
            'projected_days' => $projectedDays,
            'projected_date' => $projectedDate,
            'on_track' => $goal['target_date'] ? ($projectedDate && $projectedDate <= $goal['target_date']) : null
        ];
    }
}
