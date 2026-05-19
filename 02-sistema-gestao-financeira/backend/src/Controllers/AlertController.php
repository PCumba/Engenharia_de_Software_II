<?php

namespace App\Controllers;

use App\Utils\Response;

/**
 * Controlador de Alertas e Notificações
 */
class AlertController
{
    public function index(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $db = \App\Core\Database::getInstance();

            $isRead = isset($_GET['is_read']) ? (int) $_GET['is_read'] : null;
            $sql = "SELECT * FROM alerts WHERE user_id = :user_id";
            $params = ['user_id' => $userId];

            if ($isRead !== null) {
                $sql .= " AND is_read = :is_read";
                $params['is_read'] = $isRead;
            }

            $sql .= " ORDER BY created_at DESC LIMIT 50";
            $alerts = $db->fetchAll($sql, $params);

            Response::success(['alerts' => $alerts]);
        } catch (\Exception $e) {
            error_log("Alerts index error: " . $e->getMessage());
            Response::error('Erro ao listar alertas', 500);
        }
    }

    public function create(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['title']) || empty($data['message'])) {
                Response::validation(['Título e mensagem são obrigatórios']);
                return;
            }

            $db = \App\Core\Database::getInstance();
            $id = $db->insert('alerts', [
                'user_id' => $userId,
                'type' => $data['type'] ?? 'custom',
                'title' => $data['title'],
                'message' => $data['message'],
                'priority' => $data['priority'] ?? 'medium'
            ]);

            Response::success(['alert_id' => $id], 'Alerta criado com sucesso', 201);
        } catch (\Exception $e) {
            Response::error('Erro ao criar alerta', 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $db = \App\Core\Database::getInstance();

            $alert = $db->fetch("SELECT * FROM alerts WHERE id = :id AND user_id = :user_id", [
                'id' => $id, 'user_id' => $userId
            ]);

            if (!$alert) {
                Response::notFound('Alerta não encontrado');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $db->update('alerts', $data, ['id' => $id]);

            Response::success(null, 'Alerta atualizado');
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar alerta', 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $db = \App\Core\Database::getInstance();

            $db->delete('alerts', ['id' => $id, 'user_id' => $userId]);
            Response::success(null, 'Alerta removido');
        } catch (\Exception $e) {
            Response::error('Erro ao remover alerta', 500);
        }
    }

    public function markAsRead(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $db = \App\Core\Database::getInstance();

            $db->update('alerts', ['is_read' => 1], ['id' => $id, 'user_id' => $userId]);
            Response::success(null, 'Alerta marcado como lido');
        } catch (\Exception $e) {
            Response::error('Erro ao marcar alerta', 500);
        }
    }
}
