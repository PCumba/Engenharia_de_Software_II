<?php

namespace App\Controllers;

use App\Models\User;
use App\Utils\Response;
use App\Utils\Validator;

/**
 * Controlador de Perfil do Utilizador
 */
class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function getProfile(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $user = $this->userModel->find($userId);

            if (!$user) {
                Response::notFound('Utilizador não encontrado');
                return;
            }

            unset($user['password']);
            Response::success(['user' => $user]);
        } catch (\Exception $e) {
            Response::error('Erro ao buscar perfil', 500);
        }
    }

    public function updateProfile(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            // Campos permitidos para atualização
            $allowed = ['name', 'phone', 'timezone', 'currency', 'language', 'avatar'];
            $updateData = array_intersect_key($data, array_flip($allowed));

            if (empty($updateData)) {
                Response::validation(['Nenhum campo válido para atualização']);
                return;
            }

            $this->userModel->update($userId, $updateData);
            $user = $this->userModel->find($userId);
            unset($user['password']);

            Response::success(['user' => $user], 'Perfil atualizado com sucesso');
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar perfil', 500);
        }
    }

    public function changePassword(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            $validator = new Validator($data);
            $validator->required(['current_password', 'new_password']);
            $validator->minLength('new_password', 8);

            if (!$validator->isValid()) {
                Response::validation($validator->getErrors());
                return;
            }

            $user = $this->userModel->find($userId);

            if (!$this->userModel->verifyPassword($data['current_password'], $user['password'])) {
                Response::error('Senha atual incorreta', 400);
                return;
            }

            $hashedPassword = $this->userModel->hashPassword($data['new_password']);
            $this->userModel->update($userId, ['password' => $hashedPassword]);

            Response::success(null, 'Senha alterada com sucesso');
        } catch (\Exception $e) {
            Response::error('Erro ao alterar senha', 500);
        }
    }
}
