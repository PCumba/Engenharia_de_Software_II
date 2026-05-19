<?php

namespace App\Controllers;

use App\Models\Account;
use App\Utils\Response;

/**
 * Controlador de Transferências entre Contas
 */
class TransferController
{
    private Account $accountModel;

    public function __construct()
    {
        $this->accountModel = new Account();
    }

    public function index(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $db = \App\Core\Database::getInstance();

            $transfers = $db->fetchAll(
                "SELECT t.*, fa.name as from_account_name, ta.name as to_account_name
                 FROM transfers t
                 LEFT JOIN accounts fa ON t.from_account_id = fa.id
                 LEFT JOIN accounts ta ON t.to_account_id = ta.id
                 WHERE t.user_id = :user_id
                 ORDER BY t.transfer_date DESC",
                ['user_id' => $userId]
            );

            Response::success(['transfers' => $transfers]);
        } catch (\Exception $e) {
            error_log("Transfers index error: " . $e->getMessage());
            Response::error('Erro ao listar transferências', 500);
        }
    }

    public function create(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['from_account_id']) || empty($data['to_account_id']) || empty($data['amount'])) {
                Response::validation(['Conta de origem, destino e valor são obrigatórios']);
                return;
            }

            if ($data['from_account_id'] == $data['to_account_id']) {
                Response::validation(['Conta de origem e destino devem ser diferentes']);
                return;
            }

            if (!$this->accountModel->belongsToUser($data['from_account_id'], $userId) ||
                !$this->accountModel->belongsToUser($data['to_account_id'], $userId)) {
                Response::forbidden('Contas não pertencem ao utilizador');
                return;
            }

            // Realizar transferência
            $success = $this->accountModel->transfer(
                $data['from_account_id'],
                $data['to_account_id'],
                (float) $data['amount']
            );

            if (!$success) {
                Response::error('Erro ao realizar transferência', 500);
                return;
            }

            // Registar na tabela de transferências
            $db = \App\Core\Database::getInstance();
            $transferId = $db->insert('transfers', [
                'user_id' => $userId,
                'from_account_id' => $data['from_account_id'],
                'to_account_id' => $data['to_account_id'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? 'Transferência entre contas',
                'transfer_date' => $data['transfer_date'] ?? date('Y-m-d'),
                'status' => 'completed'
            ]);

            Response::success(['transfer_id' => $transferId], 'Transferência realizada com sucesso', 201);
        } catch (\Exception $e) {
            error_log("Transfer create error: " . $e->getMessage());
            Response::error('Erro ao realizar transferência', 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $db = \App\Core\Database::getInstance();

            $transfer = $db->fetch(
                "SELECT t.*, fa.name as from_account_name, ta.name as to_account_name
                 FROM transfers t
                 LEFT JOIN accounts fa ON t.from_account_id = fa.id
                 LEFT JOIN accounts ta ON t.to_account_id = ta.id
                 WHERE t.id = :id AND t.user_id = :user_id",
                ['id' => $id, 'user_id' => $userId]
            );

            if (!$transfer) {
                Response::notFound('Transferência não encontrada');
                return;
            }

            Response::success(['transfer' => $transfer]);
        } catch (\Exception $e) {
            Response::error('Erro ao buscar transferência', 500);
        }
    }
}
