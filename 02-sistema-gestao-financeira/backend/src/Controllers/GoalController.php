<?php

namespace App\Controllers;

use App\Models\Goal;
use App\Utils\Response;

/**
 * Controlador de Metas Financeiras
 */
class GoalController
{
    private Goal $goalModel;

    public function __construct()
    {
        $this->goalModel = new Goal();
    }

    public function index(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $goals = $this->goalModel->getUserGoals($userId);
            Response::success(['goals' => $goals]);
        } catch (\Exception $e) {
            error_log("Goals index error: " . $e->getMessage());
            Response::error('Erro ao listar metas', 500);
        }
    }

    public function create(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['name']) || empty($data['target_amount'])) {
                Response::validation(['Nome e valor alvo são obrigatórios']);
                return;
            }

            $data['user_id'] = $userId;
            $data['current_amount'] = $data['current_amount'] ?? 0;
            $id = $this->goalModel->create($data);

            Response::success(['goal' => $this->goalModel->find($id)], 'Meta criada com sucesso', 201);
        } catch (\Exception $e) {
            error_log("Goal create error: " . $e->getMessage());
            Response::error('Erro ao criar meta', 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $goal = $this->goalModel->find($id);

            if (!$goal || $goal['user_id'] != $userId) {
                Response::notFound('Meta não encontrada');
                return;
            }

            $projection = $this->goalModel->getProjection($id);
            Response::success(['goal' => $goal, 'projection' => $projection]);
        } catch (\Exception $e) {
            Response::error('Erro ao buscar meta', 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $goal = $this->goalModel->find($id);

            if (!$goal || $goal['user_id'] != $userId) {
                Response::notFound('Meta não encontrada');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $this->goalModel->update($id, $data);

            Response::success(['goal' => $this->goalModel->find($id)], 'Meta atualizada');
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar meta', 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $goal = $this->goalModel->find($id);

            if (!$goal || $goal['user_id'] != $userId) {
                Response::notFound('Meta não encontrada');
                return;
            }

            $this->goalModel->delete($id);
            Response::success(null, 'Meta removida com sucesso');
        } catch (\Exception $e) {
            Response::error('Erro ao remover meta', 500);
        }
    }

    public function updateProgress(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $goal = $this->goalModel->find($id);

            if (!$goal || $goal['user_id'] != $userId) {
                Response::notFound('Meta não encontrada');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $amount = (float) ($data['amount'] ?? 0);

            if ($amount <= 0) {
                Response::validation(['Valor deve ser positivo']);
                return;
            }

            $this->goalModel->updateProgress($id, $amount);
            $updatedGoal = $this->goalModel->find($id);

            Response::success(['goal' => $updatedGoal], 'Progresso atualizado');
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar progresso', 500);
        }
    }
}
