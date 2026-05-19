<?php

namespace App\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Utils\Response;

/**
 * Controlador de Relatórios
 */
class ReportController
{
    private Transaction $transactionModel;
    private Account $accountModel;

    public function __construct()
    {
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
    }

    /**
     * Dashboard principal
     */
    public function dashboard(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $period = $_GET['period'] ?? 'month';

            // Carregar dados em paralelo (PHP não tem async nativo, mas podemos otimizar as queries)
            $summary = $this->transactionModel->getFinancialSummary($userId, $period);
            $expensesByCategory = $this->transactionModel->getExpensesByCategory($userId, $period);
            $monthlyEvolution = $this->transactionModel->getMonthlyEvolution($userId, 6);
            $accounts = $this->accountModel->getUserAccounts($userId);
            $recentTransactions = $this->getRecentTransactions($userId);
            $goals = $this->getGoalsSummary($userId);
            $budgets = $this->getBudgetsSummary($userId);
            $alerts = $this->getUnreadAlerts($userId);

            // Calcular variação em relação ao mês anterior
            $previousSummary = $this->transactionModel->getFinancialSummary($userId, 'previous_month');
            $balanceChange = $summary['balance'] - ($previousSummary['balance'] ?? 0);
            $previousBalance = $previousSummary['balance'] ?? 0;
            $balanceChangePercentage = $previousBalance != 0
                ? (($balanceChange / abs($previousBalance)) * 100)
                : 0;

            // Saldo total de todas as contas
            $totalBalance = $this->accountModel->getTotalBalance($userId);

            Response::success([
                'summary' => [
                    'totalBalance' => $totalBalance,
                    'monthlyIncome' => $summary['income']['total'],
                    'monthlyExpense' => $summary['expense']['total'],
                    'monthlyBalance' => $summary['balance'],
                    'previousMonthBalance' => $previousBalance,
                    'balanceChange' => $balanceChange,
                    'balanceChangePercentage' => round($balanceChangePercentage, 2)
                ],
                'expensesByCategory' => $expensesByCategory,
                'recentTransactions' => $recentTransactions,
                'goals' => $goals,
                'monthlyEvolution' => $monthlyEvolution,
                'accounts' => $accounts,
                'budgets' => $budgets,
                'alerts' => $alerts
            ]);

        } catch (\Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            Response::error('Erro ao carregar dashboard', 500);
        }
    }

    /**
     * Relatório de receitas e despesas
     */
    public function incomeExpense(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $period = $_GET['period'] ?? 'month';
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;

            $summary = $this->transactionModel->getFinancialSummary($userId, $period);
            $monthlyEvolution = $this->transactionModel->getMonthlyEvolution($userId, 12);

            // Detalhamento por semana do mês atual
            $weeklyBreakdown = $this->getWeeklyBreakdown($userId);

            Response::success([
                'summary' => $summary,
                'monthly_evolution' => $monthlyEvolution,
                'weekly_breakdown' => $weeklyBreakdown,
                'period' => $period
            ]);

        } catch (\Exception $e) {
            error_log("Income expense report error: " . $e->getMessage());
            Response::error('Erro ao gerar relatório', 500);
        }
    }

    /**
     * Análise por categoria
     */
    public function categoryAnalysis(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $period = $_GET['period'] ?? 'month';
            $type = $_GET['type'] ?? 'expense';

            $byCategory = $this->transactionModel->getExpensesByCategory($userId, $period);

            // Calcular percentuais
            $total = array_sum(array_column($byCategory, 'total'));
            $byCategory = array_map(function ($item) use ($total) {
                $item['percentage'] = $total > 0 ? round(($item['total'] / $total) * 100, 2) : 0;
                return $item;
            }, $byCategory);

            // Top 5 categorias
            $top5 = array_slice($byCategory, 0, 5);

            Response::success([
                'expenses_by_category' => $byCategory,
                'top_categories' => $top5,
                'total' => $total,
                'period' => $period
            ]);

        } catch (\Exception $e) {
            error_log("Category analysis error: " . $e->getMessage());
            Response::error('Erro ao gerar análise', 500);
        }
    }

    /**
     * Resumo mensal
     */
    public function monthlySummary(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $months = (int) ($_GET['months'] ?? 12);

            $evolution = $this->transactionModel->getMonthlyEvolution($userId, $months);

            // Calcular médias
            $avgIncome = count($evolution) > 0
                ? array_sum(array_column($evolution, 'income')) / count($evolution)
                : 0;
            $avgExpense = count($evolution) > 0
                ? array_sum(array_column($evolution, 'expense')) / count($evolution)
                : 0;

            Response::success([
                'monthly_evolution' => $evolution,
                'averages' => [
                    'income' => round($avgIncome, 2),
                    'expense' => round($avgExpense, 2),
                    'balance' => round($avgIncome - $avgExpense, 2)
                ],
                'months' => $months
            ]);

        } catch (\Exception $e) {
            error_log("Monthly summary error: " . $e->getMessage());
            Response::error('Erro ao gerar resumo mensal', 500);
        }
    }

    /**
     * Performance dos orçamentos
     */
    public function budgetPerformance(): void
    {
        try {
            $userId = $_SESSION['user_id'];

            $budgets = $this->getBudgetsSummary($userId);

            $stats = [
                'total_budgets' => count($budgets),
                'on_track' => count(array_filter($budgets, fn($b) => ($b['status'] ?? '') === 'ok')),
                'warning' => count(array_filter($budgets, fn($b) => ($b['status'] ?? '') === 'warning')),
                'exceeded' => count(array_filter($budgets, fn($b) => ($b['status'] ?? '') === 'exceeded'))
            ];

            Response::success([
                'budgets' => $budgets,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            error_log("Budget performance error: " . $e->getMessage());
            Response::error('Erro ao gerar relatório de orçamentos', 500);
        }
    }

    /**
     * Exportar relatório
     */
    public function export(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            $format = $data['format'] ?? 'csv';
            $type = $data['type'] ?? 'transactions';
            $filters = $data['filters'] ?? [];

            // Por simplicidade, exportar como CSV
            if ($format === 'csv') {
                $this->exportCsv($userId, $type, $filters);
            } else {
                Response::error('Formato não suportado. Use CSV.', 400);
            }

        } catch (\Exception $e) {
            error_log("Export error: " . $e->getMessage());
            Response::error('Erro ao exportar relatório', 500);
        }
    }

    /**
     * Exportar como CSV
     */
    private function exportCsv(int $userId, string $type, array $filters): void
    {
        $transactionModel = new Transaction();
        $result = $transactionModel->getUserTransactions($userId, $filters, 1, 10000);
        $transactions = $result['data'];

        // Configurar headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="transacoes_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalho
        fputcsv($output, [
            'ID', 'Tipo', 'Descrição', 'Valor', 'Data',
            'Categoria', 'Conta', 'Método de Pagamento',
            'Status', 'Notas', 'Criado em'
        ], ';');

        // Dados
        foreach ($transactions as $t) {
            fputcsv($output, [
                $t['id'],
                $t['type'],
                $t['description'],
                number_format($t['amount'], 2, ',', '.'),
                $t['transaction_date'],
                $t['category_name'] ?? '',
                $t['account_name'] ?? '',
                $t['payment_method'] ?? '',
                $t['status'],
                $t['notes'] ?? '',
                $t['created_at']
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Obter transações recentes
     */
    private function getRecentTransactions(int $userId, int $limit = 10): array
    {
        $result = $this->transactionModel->getUserTransactions($userId, [], 1, $limit);
        return $result['data'];
    }

    /**
     * Obter resumo das metas
     */
    private function getGoalsSummary(int $userId): array
    {
        $db = $this->transactionModel->query(
            "SELECT id, name, target_amount, current_amount, target_date, category, priority,
                    ROUND((current_amount / target_amount) * 100, 2) as percentage
             FROM goals
             WHERE user_id = :user_id AND is_active = 1
             ORDER BY priority DESC, target_date ASC
             LIMIT 5",
            ['user_id' => $userId]
        );

        return array_map(function ($goal) {
            $goal['percentage'] = min((float) $goal['percentage'], 100);
            return $goal;
        }, $db);
    }

    /**
     * Obter resumo dos orçamentos
     */
    private function getBudgetsSummary(int $userId): array
    {
        $db = $this->transactionModel->query(
            "SELECT b.id, b.name, b.amount, b.period, b.alert_percentage,
                    c.name as category_name, c.color as category_color,
                    COALESCE(SUM(t.amount), 0) as spent
             FROM budgets b
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
             ORDER BY b.created_at DESC",
            ['user_id' => $userId]
        );

        return array_map(function ($budget) {
            $spent = (float) $budget['spent'];
            $amount = (float) $budget['amount'];
            $percentage = $amount > 0 ? ($spent / $amount) * 100 : 0;
            $remaining = $amount - $spent;

            $status = 'ok';
            if ($percentage >= 100) $status = 'exceeded';
            elseif ($percentage >= $budget['alert_percentage']) $status = 'warning';

            return array_merge($budget, [
                'spent' => $spent,
                'remaining' => $remaining,
                'percentage' => round($percentage, 2),
                'status' => $status
            ]);
        }, $db);
    }

    /**
     * Obter alertas não lidos
     */
    private function getUnreadAlerts(int $userId): array
    {
        return $this->transactionModel->query(
            "SELECT id, type, title, message, priority, created_at
             FROM alerts
             WHERE user_id = :user_id AND is_read = 0
             ORDER BY priority DESC, created_at DESC
             LIMIT 5",
            ['user_id' => $userId]
        );
    }

    /**
     * Breakdown semanal do mês atual
     */
    private function getWeeklyBreakdown(int $userId): array
    {
        return $this->transactionModel->query(
            "SELECT
                WEEK(transaction_date, 1) as week_number,
                CONCAT('Semana ', WEEK(transaction_date, 1) - WEEK(DATE_FORMAT(CURDATE(), '%Y-%m-01'), 1) + 1) as week_label,
                type,
                SUM(amount) as total
             FROM transactions
             WHERE user_id = :user_id
             AND status = 'completed'
             AND MONTH(transaction_date) = MONTH(CURDATE())
             AND YEAR(transaction_date) = YEAR(CURDATE())
             GROUP BY week_number, type
             ORDER BY week_number ASC",
            ['user_id' => $userId]
        );
    }
}