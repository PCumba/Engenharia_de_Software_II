<?php

namespace App\Controllers;

use App\Models\Account;
use App\Utils\Response;
use App\Utils\Validator;

/**
 * Controlador de Contas Bancárias
 */
class AccountController
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
            $accounts = $this->accountModel->getUserAccounts($userId, false);
            Response::success(['accounts' => $accounts]);
        } catch (\Exception $e) {
            error_log("Accounts index error: " . $e->getMessage());
            Response::error('Erro ao listar contas', 500);
        }
    }

    public function create(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            $errors = $this->accountModel->validateAccountData($data);
            if (!empty($errors)) {
                Response::validation($errors);
                return;
            }

            $data['user_id'] = $userId;
            $data['current_balance'] = $data['initial_balance'] ?? 0;
            $id = $this->accountModel->create($data);

            Response::success(['account' => $this->accountModel->find($id)], 'Conta criada com sucesso', 201);
        } catch (\Exception $e) {
            error_log("Account create error: " . $e->getMessage());
            Response::error('Erro ao criar conta', 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $account = $this->accountModel->find($id);

            if (!$account || $account['user_id'] != $userId) {
                Response::notFound('Conta não encontrada');
                return;
            }

            Response::success(['account' => $account]);
        } catch (\Exception $e) {
            error_log("Account show error: " . $e->getMessage());
            Response::error('Erro ao buscar conta', 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $account = $this->accountModel->find($id);

            if (!$account || $account['user_id'] != $userId) {
                Response::notFound('Conta não encontrada');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $this->accountModel->update($id, $data);

            Response::success(['account' => $this->accountModel->find($id)], 'Conta atualizada com sucesso');
        } catch (\Exception $e) {
            error_log("Account update error: " . $e->getMessage());
            Response::error('Erro ao atualizar conta', 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $account = $this->accountModel->find($id);

            if (!$account || $account['user_id'] != $userId) {
                Response::notFound('Conta não encontrada');
                return;
            }

            $this->accountModel->delete($id);
            Response::success(null, 'Conta removida com sucesso');
        } catch (\Exception $e) {
            error_log("Account delete error: " . $e->getMessage());
            Response::error('Erro ao remover conta', 500);
        }
    }

    public function getBalance(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            if (!$this->accountModel->belongsToUser($id, $userId)) {
                Response::notFound('Conta não encontrada');
                return;
            }

            $account = $this->accountModel->find($id);
            Response::success([
                'account_id' => $id,
                'balance' => (float) $account['current_balance'],
                'currency' => $account['currency']
            ]);
        } catch (\Exception $e) {
            error_log("Account balance error: " . $e->getMessage());
            Response::error('Erro ao buscar saldo', 500);
        }
    }
}
