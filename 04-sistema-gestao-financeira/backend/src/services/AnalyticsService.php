<?php
/**
 * Serviço de Análises
 */

class AnalyticsService {
    private $transactionModel;

    public function __construct($database) {
        $this->transactionModel = new Transaction($database);
    }

    /**
     * Obter despesas por categoria
     */
    public function getExpensesByCategory($userId, $startDate, $endDate) {
        try {
            $transactions = $this->transactionModel->getByUserAndPeriod($userId, $startDate, $endDate);
            
            $categories = [];
            foreach ($transactions as $t) {
                if ($t['type'] === 'expense') {
                    $cat = $t['category_name'] ?: 'Sem categoria';
                    if (!isset($categories[$cat])) {
                        $categories[$cat] = ['amount' => 0, 'color' => $t['color']];
                    }
                    $categories[$cat]['amount'] += (float)$t['amount'];
                }
            }
            
            return array_values($categories);
        } catch (Exception $e) {
            throw new Exception("Erro ao analisar despesas: " . $e->getMessage());
        }
    }

    /**
     * Obter receitas por categoria
     */
    public function getIncomeByCategory($userId, $startDate, $endDate) {
        try {
            $transactions = $this->transactionModel->getByUserAndPeriod($userId, $startDate, $endDate);
            
            $categories = [];
            foreach ($transactions as $t) {
                if ($t['type'] === 'income') {
                    $cat = $t['category_name'] ?: 'Sem categoria';
                    if (!isset($categories[$cat])) {
                        $categories[$cat] = ['amount' => 0, 'color' => $t['color']];
                    }
                    $categories[$cat]['amount'] += (float)$t['amount'];
                }
            }
            
            return array_values($categories);
        } catch (Exception $e) {
            throw new Exception("Erro ao analisar receitas: " . $e->getMessage());
        }
    }

    /**
     * Obter evolução mensal
     */
    public function getMonthlyEvolution($userId, $year) {
        try {
            $data = [];
            
            for ($month = 1; $month <= 12; $month++) {
                $startDate = date("$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01");
                $endDate = date("$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-t");
                
                $transactions = $this->transactionModel->getByUserAndPeriod($userId, $startDate, $endDate);
                
                $income = 0;
                $expenses = 0;
                
                foreach ($transactions as $t) {
                    if ($t['type'] === 'income') {
                        $income += (float)$t['amount'];
                    } else {
                        $expenses += (float)$t['amount'];
                    }
                }
                
                $data[] = [
                    'month' => date('M', mktime(0, 0, 0, $month)),
                    'income' => $income,
                    'expenses' => $expenses,
                    'balance' => $income - $expenses
                ];
            }
            
            return $data;
        } catch (Exception $e) {
            throw new Exception("Erro ao obter evolução: " . $e->getMessage());
        }
    }

    /**
     * Gerar relatório de período
     */
    public function generatePeriodReport($userId, $startDate, $endDate) {
        try {
            $transactions = $this->transactionModel->getByUserAndPeriod($userId, $startDate, $endDate);
            
            $totalIncome = 0;
            $totalExpenses = 0;
            $categoryExpenses = [];
            $categoryIncome = [];
            
            foreach ($transactions as $t) {
                if ($t['type'] === 'income') {
                    $totalIncome += (float)$t['amount'];
                    $cat = $t['category_name'] ?: 'Sem categoria';
                    $categoryIncome[$cat] = ($categoryIncome[$cat] ?? 0) + (float)$t['amount'];
                } else {
                    $totalExpenses += (float)$t['amount'];
                    $cat = $t['category_name'] ?: 'Sem categoria';
                    $categoryExpenses[$cat] = ($categoryExpenses[$cat] ?? 0) + (float)$t['amount'];
                }
            }
            
            return [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'totalIncome' => $totalIncome,
                'totalExpenses' => $totalExpenses,
                'balance' => $totalIncome - $totalExpenses,
                'categoryExpenses' => $categoryExpenses,
                'categoryIncome' => $categoryIncome,
                'transactionCount' => count($transactions)
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao gerar relatório: " . $e->getMessage());
        }
    }
}
?>
