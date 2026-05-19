<?php

namespace App\Controllers;

use App\Models\Budget;
use App\Utils\Response;

/**
 * Controlador de Orçamentos
 */
class BudgetController
{
    private Budget $budgetModel;

    public function __construct()
    {
        $this->budgetModel = new Budget();
    }

    public function index(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $budgets = $this->budgetModel->getUserBudgets($userId);
            Response::success(['budgets' => $budgets]);
        } catch (\Exception $e) {
            error_log("Budgets index error: " . $e->getMessage());
            Response::error('Erro ao listar orçamentos', 500);
        }
    }

    public function create(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['name']) || empty($data['amount'])) {
                Response::validation(['Nome e valor são obrigatórios']);
                return;
            }

            $data['user_id'] = $userId;
            $data['start_date'] = $data['start_date'] ?? date('Y-m-01');
            $id = $this->budgetModel->create($data);

            Response::success(['budget' => $this->budgetModel->find($id)], 'Orçamento criado com sucesso', 201);
        } catch (\Exception $e) {
            error_log("Budget create error: " . $e->getMessage());
            Response::error('Erro ao criar orçamento', 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $budget = $this->budgetModel->find($id);

            if (!$budget || $budget['user_id'] != $userId) {
                Response::notFound('Orçamento não encontrado');
                return;
            }

            Response::success(['budget' => $budget]);
        } catch (\Exception $e) {
            Response::error('Erro ao buscar orçamento', 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $budget = $this->budgetModel->find($id);

            if (!$budget || $budget['user_id'] != $userId) {
                Response::notFound('Orçamento não encontrado');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $this->budgetModel->update($id, $data);

            Response::success(['budget' => $this->budgetModel->find($id)], 'Orçamento atualizado');
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar orçamento', 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $budget = $this->budgetModel->find($id);

            if (!$budget || $budget['user_id'] != $userId) {
                Response::notFound('Orçamento não encontrado');
                return;
            }

            $this->budgetModel->delete($id);
            Response::success(null, 'Orçamento removido com sucesso');
        } catch (\Exception $e) {
            Response::error('Erro ao remover orçamento', 500);
        }
    }

    public function getStatus(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $status = $this->budgetModel->getBudgetStatus($id);

            if (!$status || $status['budget']['user_id'] != $userId) {
                Response::notFound('Orçamento não encontrado');
                return;
            }

            Response::success($status);
        } catch (\Exception $e) {
            Response::error('Erro ao buscar status do orçamento', 500);
        }
    }
}
