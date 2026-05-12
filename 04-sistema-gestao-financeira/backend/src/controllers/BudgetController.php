<?php
/**
 * Controller de Orçamentos
 */

class BudgetController {
    private $financeService;
    private $budgetModel;

    public function __construct($database) {
        $this->financeService = new FinanceService($database);
        $this->budgetModel = new Budget($database);
    }

    public function getForMonth() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $month = $_GET['month'] ?? date('m');
            $year = $_GET['year'] ?? date('Y');

            $budgets = $this->financeService->getBudgetsForMonth($token['userId'], $month, $year);
            $status = $this->financeService->checkBudgetStatus($token['userId'], $month, $year);

            return Response::success('Orçamentos', ['budgets' => $budgets, 'status' => $status]);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function create() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $data = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'categoryId' => 'required|numeric',
                'limitAmount' => 'required|numeric'
            ];

            $errors = Validator::validate($data, $rules);
            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            $id = $this->financeService->createBudget(
                $token['userId'],
                $data['categoryId'],
                $data['limitAmount'],
                $data['month'] ?? null,
                $data['year'] ?? null
            );

            return Response::success('Orçamento criado', ['id' => $id], 201);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function update($budgetId) {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $data = json_decode(file_get_contents('php://input'), true);

            $this->budgetModel->update($budgetId, $token['userId'], $data['limitAmount']);
            return Response::success('Orçamento atualizado');
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function delete($budgetId) {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $this->budgetModel->delete($budgetId, $token['userId']);
            return Response::success('Orçamento removido');
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function checkStatus() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $month = $_GET['month'] ?? date('m');
            $year = $_GET['year'] ?? date('Y');

            $status = $this->financeService->checkBudgetStatus($token['userId'], $month, $year);
            return Response::success('Status dos orçamentos', $status);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
