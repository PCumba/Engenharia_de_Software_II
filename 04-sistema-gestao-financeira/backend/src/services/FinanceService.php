<?php
/**
 * Serviço de Finanças
 */

class FinanceService {
    private $transactionModel;
    private $categoryModel;
    private $budgetModel;

    public function __construct($database) {
        $this->transactionModel = new Transaction($database);
        $this->categoryModel = new Category($database);
        $this->budgetModel = new Budget($database);
    }

    /**
     * Obter resumo financeiro do utilizador
     */
    public function getSummary($userId) {
        try {
            $balance = $this->transactionModel->getBalance($userId);
            $currentMonth = date('m');
            $currentYear = date('Y');
            
            $budgets = $this->budgetModel->getByUserAndMonth($userId, $currentMonth, $currentYear);
            $categories = $this->categoryModel->getByUser($userId);

            return [
                'balance' => $balance,
                'budgets' => $budgets,
                'categories' => $categories
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao obter resumo: " . $e->getMessage());
        }
    }

    /**
     * Adicionar transação
     */
    public function addTransaction($userId, $categoryId, $description, $amount, $type, $date = null) {
        try {
            return $this->transactionModel->create($userId, $categoryId, $description, $amount, $type, $date);
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar transação: " . $e->getMessage());
        }
    }

    /**
     * Obter transações por período
     */
    public function getTransactionsByPeriod($userId, $startDate, $endDate) {
        try {
            return $this->transactionModel->getByUserAndPeriod($userId, $startDate, $endDate);
        } catch (Exception $e) {
            throw new Exception("Erro ao obter transações: " . $e->getMessage());
        }
    }

    /**
     * Criar orçamento
     */
    public function createBudget($userId, $categoryId, $limitAmount, $month = null, $year = null) {
        try {
            $month = $month ?: date('m');
            $year = $year ?: date('Y');
            
            return $this->budgetModel->create($userId, $categoryId, $limitAmount, $month, $year);
        } catch (Exception $e) {
            throw new Exception("Erro ao criar orçamento: " . $e->getMessage());
        }
    }

    /**
     * Obter orçamentos do mês
     */
    public function getBudgetsForMonth($userId, $month = null, $year = null) {
        try {
            $month = $month ?: date('m');
            $year = $year ?: date('Y');
            
            return $this->budgetModel->getByUserAndMonth($userId, $month, $year);
        } catch (Exception $e) {
            throw new Exception("Erro ao obter orçamentos: " . $e->getMessage());
        }
    }

    /**
     * Verificar status dos orçamentos
     */
    public function checkBudgetStatus($userId, $month = null, $year = null) {
        try {
            $budgets = $this->getBudgetsForMonth($userId, $month, $year);
            
            $status = [];
            foreach ($budgets as $budget) {
                $percentage = ($budget['spent'] / $budget['limit_amount']) * 100;
                $status[] = [
                    'budget_id' => $budget['id'],
                    'category' => $budget['category_name'],
                    'limit' => $budget['limit_amount'],
                    'spent' => $budget['spent'],
                    'remaining' => $budget['limit_amount'] - $budget['spent'],
                    'percentage' => round($percentage, 2),
                    'exceeded' => $budget['spent'] > $budget['limit_amount']
                ];
            }
            
            return $status;
        } catch (Exception $e) {
            throw new Exception("Erro ao verificar status: " . $e->getMessage());
        }
    }
}
?>
