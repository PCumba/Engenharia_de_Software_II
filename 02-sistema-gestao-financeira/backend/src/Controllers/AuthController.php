<?php

namespace App\Controllers;

use App\Models\User;
use App\Utils\Response;
use App\Utils\Validator;
use App\Services\JWTService;
use App\Services\EmailService;

/**
 * Controlador de Autenticação
 */
class AuthController
{
    private User $userModel;
    private JWTService $jwtService;
    private EmailService $emailService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jwtService = new JWTService();
        $this->emailService = new EmailService();
    }

    /**
     * Login do usuário
     */
    public function login(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar dados
            $validator = new Validator($data);
            $validator->required(['email', 'password']);
            $validator->email('email');
            
            if (!$validator->isValid()) {
                Response::error('Dados inválidos', 400, $validator->getErrors());
                return;
            }
            
            // Buscar usuário
            $user = $this->userModel->findByEmail($data['email']);
            
            if (!$user || !$this->userModel->verifyPassword($data['password'], $user['password'])) {
                Response::error('Credenciais inválidas', 401);
                return;
            }
            
            if (!$user['is_active']) {
                Response::error('Conta desativada', 403);
                return;
            }
            
            // Gerar tokens
            $accessToken = $this->jwtService->generateToken($user['id']);
            $refreshToken = $this->jwtService->generateRefreshToken($user['id']);
            
            // Atualizar último login
            $this->userModel->updateLastLogin($user['id']);
            
            // Remover senha da resposta
            unset($user['password']);
            
            Response::success([
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ], 'Login realizado com sucesso');
            
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }

    /**
     * Registro de novo usuário
     */
    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar dados
            $validator = new Validator($data);
            $validator->required(['name', 'email', 'password']);
            $validator->email('email');
            $validator->minLength('password', 8);
            $validator->minLength('name', 2);
            
            if (!$validator->isValid()) {
                Response::error('Dados inválidos', 400, $validator->getErrors());
                return;
            }
            
            // Verificar se email já existe
            if ($this->userModel->emailExists($data['email'])) {
                Response::error('Email já cadastrado', 409);
                return;
            }
            
            // Preparar dados do usuário
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $this->userModel->hashPassword($data['password']),
                'phone' => $data['phone'] ?? null,
                'timezone' => $data['timezone'] ?? 'America/Sao_Paulo',
                'currency' => $data['currency'] ?? 'BRL',
                'language' => $data['language'] ?? 'pt-BR',
                'is_active' => true
            ];
            
            // Criar usuário
            $userId = $this->userModel->create($userData);
            
            // Buscar usuário criado
            $user = $this->userModel->find($userId);
            unset($user['password']);
            
            // Gerar tokens
            $accessToken = $this->jwtService->generateToken($userId);
            $refreshToken = $this->jwtService->generateRefreshToken($userId);
            
            // Enviar email de boas-vindas (opcional)
            try {
                $this->emailService->sendWelcomeEmail($user['email'], $user['name']);
            } catch (\Exception $e) {
                error_log("Welcome email error: " . $e->getMessage());
            }
            
            Response::success([
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ], 'Usuário registrado com sucesso', 201);
            
        } catch (\Exception $e) {
            error_log("Register error: " . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }

    /**
     * Logout do usuário
     */
    public function logout(): void
    {
        try {
            // Em uma implementação completa, você invalidaria o token
            // Por simplicidade, apenas retornamos sucesso
            Response::success(null, 'Logout realizado com sucesso');
            
        } catch (\Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }

    /**
     * Renovar token de acesso
     */
    public function refresh(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['refresh_token'])) {
                Response::error('Refresh token é obrigatório', 400);
                return;
            }
            
            // Validar refresh token
            $payload = $this->jwtService->validateRefreshToken($data['refresh_token']);
            
            if (!$payload) {
                Response::error('Refresh token inválido', 401);
                return;
            }
            
            // Verificar se usuário ainda existe e está ativo
            $user = $this->userModel->find($payload['user_id']);
            
            if (!$user || !$user['is_active']) {
                Response::error('Usuário inválido', 401);
                return;
            }
            
            // Gerar novos tokens
            $accessToken = $this->jwtService->generateToken($user['id']);
            $refreshToken = $this->jwtService->generateRefreshToken($user['id']);
            
            Response::success([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ], 'Token renovado com sucesso');
            
        } catch (\Exception $e) {
            error_log("Refresh token error: " . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }

    /**
     * Solicitar recuperação de senha
     */
    public function forgotPassword(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar email
            $validator = new Validator($data);
            $validator->required(['email']);
            $validator->email('email');
            
            if (!$validator->isValid()) {
                Response::error('Email inválido', 400, $validator->getErrors());
                return;
            }
            
            // Buscar usuário
            $user = $this->userModel->findByEmail($data['email']);
            
            // Por segurança, sempre retornamos sucesso mesmo se o email não existir
            if ($user) {
                // Gerar token de recuperação
                $resetToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Salvar token (implementar tabela password_reset_tokens)
                // Por simplicidade, vamos apenas enviar o email
                
                try {
                    $this->emailService->sendPasswordResetEmail(
                        $user['email'], 
                        $user['name'], 
                        $resetToken
                    );
                } catch (\Exception $e) {
                    error_log("Password reset email error: " . $e->getMessage());
                }
            }
            
            Response::success(null, 'Se o email existir, você receberá instruções para recuperação');
            
        } catch (\Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }

    /**
     * Redefinir senha
     */
    public function resetPassword(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar dados
            $validator = new Validator($data);
            $validator->required(['token', 'password']);
            $validator->minLength('password', 8);
            
            if (!$validator->isValid()) {
                Response::error('Dados inválidos', 400, $validator->getErrors());
                return;
            }
            
            // Validar token (implementar verificação na tabela password_reset_tokens)
            // Por simplicidade, vamos assumir que o token é válido
            
            // Buscar usuário pelo token (implementar)
            // Por simplicidade, vamos usar um email fictício
            if (empty($data['email'])) {
                Response::error('Token inválido ou expirado', 400);
                return;
            }
            
            $user = $this->userModel->findByEmail($data['email']);
            
            if (!$user) {
                Response::error('Token inválido ou expirado', 400);
                return;
            }
            
            // Atualizar senha
            $hashedPassword = $this->userModel->hashPassword($data['password']);
            $this->userModel->update($user['id'], ['password' => $hashedPassword]);
            
            // Invalidar token (implementar)
            
            Response::success(null, 'Senha redefinida com sucesso');
            
        } catch (\Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }

    /**
     * Verificar se usuário está autenticado
     */
    public function me(): void
    {
        try {
            // O middleware de autenticação já validou o token
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                Response::error('Não autenticado', 401);
                return;
            }
            
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                Response::error('Usuário não encontrado', 404);
                return;
            }
            
            unset($user['password']);
            
            Response::success(['user' => $user]);
            
        } catch (\Exception $e) {
            error_log("Me error: " . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
}