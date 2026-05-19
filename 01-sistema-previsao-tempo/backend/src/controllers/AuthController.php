<?php
/**
 * Controlador de Autenticação
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PasswordResetToken.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthController {
    private $db;
    private $userModel;
    private $passwordResetTokenModel;
    private $activityLogModel;
    private $emailService;

    public function __construct() {
        $this->db = new Database();
        $this->userModel = new User($this->db);
        $this->passwordResetTokenModel = new PasswordResetToken($this->db);
        $this->activityLogModel = new ActivityLog($this->db);
        $this->emailService = new EmailService();
    }

    /**
     * Registar novo usuário
     */
    public function register() {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            // Log temporário para debug
            error_log("=== FRONTEND DEBUG ===");
            error_log("Raw input: " . $rawInput);
            error_log("Parsed input: " . json_encode($input));
            error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

            // Validar dados com complexidade de senha
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:8|password_complexity',
                'name' => 'required|min:3'
            ];

            $errors = Validator::validate($input, $rules);
            
            error_log("Validation errors: " . json_encode($errors));
            
            if (!Validator::isValid($errors)) {
                $this->activityLogModel->logAuthEvent(null, 'register', false, [
                    'email' => $input['email'] ?? null,
                    'errors' => $errors
                ]);
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            // Verificar se email existe
            if ($this->userModel->emailExists($input['email'])) {
                $this->activityLogModel->logAuthEvent(null, 'register', false, [
                    'email' => $input['email'],
                    'reason' => 'email_exists'
                ]);
                return Response::json(Response::error('Email já registado', ['email' => 'Este email já existe'], 400));
            }

            // Hash da senha
            $hashedPassword = Auth::hashPassword($input['password']);

            // Criar usuário
            if ($this->userModel->create($input['email'], $hashedPassword, $input['name'])) {
                // Buscar usuário criado para obter ID
                $user = $this->userModel->findByEmail($input['email']);
                
                // Log de sucesso
                $this->activityLogModel->logAuthEvent($user['id'], 'register', true, [
                    'email' => $input['email']
                ]);

                // Enviar email de boas-vindas
                $this->emailService->sendWelcomeEmail($user);

                return Response::json(Response::success('Usuário registado com sucesso', null, 201));
            } else {
                $this->activityLogModel->logAuthEvent(null, 'register', false, [
                    'email' => $input['email'],
                    'reason' => 'database_error'
                ]);
                return Response::json(Response::error('Erro ao registar usuário', null, 500));
            }
        } catch (Exception $e) {
            $this->activityLogModel->logAuthEvent(null, 'register', false, [
                'error' => $e->getMessage()
            ]);
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Login do usuário
     */
    public function login() {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validar dados
            $rules = [
                'email' => 'required|email',
                'password' => 'required'
            ];

            $errors = Validator::validate($input, $rules);
            
            if (!Validator::isValid($errors)) {
                $this->activityLogModel->logAuthEvent(null, 'login', false, [
                    'email' => $input['email'] ?? null,
                    'errors' => $errors
                ]);
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            // Buscar usuário
            $user = $this->userModel->findByEmail($input['email']);

            if (!$user) {
                $this->activityLogModel->logAuthEvent(null, 'login', false, [
                    'email' => $input['email'],
                    'reason' => 'user_not_found'
                ]);
                return Response::json(Response::error('Email ou senha inválidos', null, 401));
            }

            // Verificar se conta está bloqueada
            if ($this->userModel->isAccountLocked($user['id'])) {
                $this->activityLogModel->logAuthEvent($user['id'], 'login', false, [
                    'email' => $input['email'],
                    'reason' => 'account_locked'
                ]);
                return Response::json(Response::error('Conta temporariamente bloqueada devido a tentativas excessivas', null, 423));
            }

            // Verificar senha
            if (!Auth::verifyPassword($input['password'], $user['password'])) {
                // Incrementar tentativas falhadas
                $this->userModel->incrementFailedAttempts($user['id']);
                
                // Verificar se deve bloquear conta
                $failedAttempts = $this->userModel->getFailedAttempts($user['id']);
                if ($failedAttempts >= 5) {
                    $this->userModel->lockAccount($user['id']);
                    $this->activityLogModel->logAuthEvent($user['id'], 'account_locked', true, [
                        'email' => $input['email'],
                        'failed_attempts' => $failedAttempts
                    ]);
                }

                $this->activityLogModel->logAuthEvent($user['id'], 'login', false, [
                    'email' => $input['email'],
                    'reason' => 'invalid_password',
                    'failed_attempts' => $failedAttempts + 1
                ]);

                return Response::json(Response::error('Email ou senha inválidos', null, 401));
            }

            // Login bem-sucedido - resetar tentativas falhadas
            $this->userModel->resetFailedAttempts($user['id']);

            // Gerar token
            $token = Auth::generateToken($user['id'], $user['email']);

            $userData = [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'language' => $user['language'],
                'theme' => $user['theme']
            ];

            // Log de sucesso
            $this->activityLogModel->logAuthEvent($user['id'], 'login', true, [
                'email' => $input['email']
            ]);

            return Response::json(Response::success('Login realizado com sucesso', [
                'token' => $token,
                'user' => $userData
            ]));
        } catch (Exception $e) {
            $this->activityLogModel->logAuthEvent(null, 'login', false, [
                'error' => $e->getMessage()
            ]);
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Obter dados do usuário autenticado
     */
    public function getCurrentUser() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $user = $this->userModel->findById($payload['userId']);

            if (!$user) {
                return Response::json(Response::error('Usuário não encontrado', null, 404));
            }

            return Response::json(Response::success('Usuário obtido com sucesso', $user));
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Atualizar preferências do usuário
     */
    public function updatePreferences() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if ($this->userModel->updatePreferences($payload['userId'], $input['language'] ?? 'pt', $input['theme'] ?? 'light')) {
                return Response::json(Response::success('Preferências atualizadas com sucesso'));
            } else {
                return Response::json(Response::error('Erro ao atualizar preferências', null, 500));
            }
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Logout (apenas retorna mensagem, pois JWT não tem estado no servidor)
     */
    public function logout() {
        try {
            $payload = Auth::checkToken();
            
            if ($payload) {
                $this->activityLogModel->logAuthEvent($payload['userId'], 'logout', true);
            }
            
            return Response::json(Response::success('Logout realizado com sucesso'));
        } catch (Exception $e) {
            return Response::json(Response::success('Logout realizado com sucesso'));
        }
    }

    /**
     * Solicitar redefinição de senha
     * Requirements: 1.4, 8.1, 8.2, 12.3
     */
    public function requestPasswordReset() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validar email
            $rules = ['email' => 'required|email'];
            $errors = Validator::validate($input, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            // Buscar usuário
            $user = $this->userModel->findByEmail($input['email']);

            // Sempre retornar sucesso por segurança (não revelar se email existe)
            if (!$user) {
                $this->activityLogModel->logAuthEvent(null, 'password_reset_request', false, [
                    'email' => $input['email'],
                    'reason' => 'user_not_found'
                ]);
                return Response::json(Response::success('Se o email existir, você receberá instruções para redefinir sua senha'));
            }

            // Verificar rate limiting (máximo 3 tentativas por hora)
            // TODO: Implementar verificação de rate limiting baseada em IP/email

            // Invalidar tokens existentes do usuário
            $this->passwordResetTokenModel->invalidateUserTokens($user['id']);

            // Gerar novo token
            $token = PasswordResetToken::generateSecureToken();
            $expiresAt = PasswordResetToken::calculateExpirationTime();

            // Salvar token no banco
            if ($this->passwordResetTokenModel->create($user['id'], $token, $expiresAt)) {
                // Enviar email
                $emailSent = $this->emailService->sendPasswordResetEmail($user, $token);
                
                $this->activityLogModel->logAuthEvent($user['id'], 'password_reset_request', true, [
                    'email' => $input['email'],
                    'email_sent' => $emailSent
                ]);

                return Response::json(Response::success('Se o email existir, você receberá instruções para redefinir sua senha'));
            } else {
                $this->activityLogModel->logAuthEvent($user['id'], 'password_reset_request', false, [
                    'email' => $input['email'],
                    'reason' => 'token_creation_failed'
                ]);
                return Response::json(Response::error('Erro interno do servidor', null, 500));
            }
        } catch (Exception $e) {
            $this->activityLogModel->logAuthEvent(null, 'password_reset_request', false, [
                'error' => $e->getMessage()
            ]);
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Validar token de redefinição de senha
     * Requirements: 8.3
     */
    public function validateResetToken() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['token'])) {
                return Response::json(Response::error('Token é obrigatório', null, 400));
            }

            $isValid = $this->passwordResetTokenModel->isTokenValid($input['token']);

            return Response::json(Response::success('Token validado', ['valid' => $isValid]));
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Redefinir senha com token
     * Requirements: 1.5, 8.3, 8.4, 8.5
     */
    public function resetPassword() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validar dados
            $rules = [
                'token' => 'required',
                'password' => 'required|min:8|password_complexity'
            ];

            $errors = Validator::validate($input, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            // Verificar se token é válido
            if (!$this->passwordResetTokenModel->isTokenValid($input['token'])) {
                $this->activityLogModel->logAuthEvent(null, 'password_reset', false, [
                    'reason' => 'invalid_token'
                ]);
                return Response::json(Response::error('Token inválido ou expirado', null, 400));
            }

            // Buscar dados do token
            $tokenData = $this->passwordResetTokenModel->findByToken($input['token']);
            $user = $this->userModel->findById($tokenData['user_id']);

            if (!$user) {
                $this->activityLogModel->logAuthEvent(null, 'password_reset', false, [
                    'reason' => 'user_not_found'
                ]);
                return Response::json(Response::error('Usuário não encontrado', null, 404));
            }

            // Hash da nova senha
            $hashedPassword = Auth::hashPassword($input['password']);

            // Atualizar senha
            if ($this->userModel->updatePassword($user['id'], $hashedPassword)) {
                // Invalidar token (marcar como usado)
                $this->passwordResetTokenModel->invalidateToken($input['token']);

                // Resetar tentativas de login falhadas
                $this->userModel->resetFailedAttempts($user['id']);

                // Log de sucesso
                $this->activityLogModel->logAuthEvent($user['id'], 'password_reset', true, [
                    'email' => $user['email']
                ]);

                // Enviar notificação de alteração de senha
                $this->emailService->sendPasswordChangedNotification($user);

                return Response::json(Response::success('Senha redefinida com sucesso'));
            } else {
                $this->activityLogModel->logAuthEvent($user['id'], 'password_reset', false, [
                    'email' => $user['email'],
                    'reason' => 'database_error'
                ]);
                return Response::json(Response::error('Erro ao redefinir senha', null, 500));
            }
        } catch (Exception $e) {
            $this->activityLogModel->logAuthEvent(null, 'password_reset', false, [
                'error' => $e->getMessage()
            ]);
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Alterar senha (usuário autenticado)
     * Requirements: 1.6, 8.5
     */
    public function changePassword() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $input = json_decode(file_get_contents('php://input'), true);

            // Validar dados
            $rules = [
                'current_password' => 'required',
                'new_password' => 'required|min:8|password_complexity'
            ];

            $errors = Validator::validate($input, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            // Buscar usuário
            $user = $this->userModel->findById($payload['userId']);

            if (!$user) {
                return Response::json(Response::error('Usuário não encontrado', null, 404));
            }

            // Verificar senha atual
            if (!Auth::verifyPassword($input['current_password'], $user['password'])) {
                $this->activityLogModel->logAuthEvent($user['id'], 'password_change', false, [
                    'reason' => 'invalid_current_password'
                ]);
                return Response::json(Response::error('Senha atual incorreta', null, 400));
            }

            // Hash da nova senha
            $hashedPassword = Auth::hashPassword($input['new_password']);

            // Atualizar senha
            if ($this->userModel->updatePassword($user['id'], $hashedPassword)) {
                // Log de sucesso
                $this->activityLogModel->logAuthEvent($user['id'], 'password_change', true);

                // Enviar notificação de alteração de senha
                $this->emailService->sendPasswordChangedNotification($user);

                return Response::json(Response::success('Senha alterada com sucesso'));
            } else {
                $this->activityLogModel->logAuthEvent($user['id'], 'password_change', false, [
                    'reason' => 'database_error'
                ]);
                return Response::json(Response::error('Erro ao alterar senha', null, 500));
            }
        } catch (Exception $e) {
            $this->activityLogModel->logAuthEvent(null, 'password_change', false, [
                'error' => $e->getMessage()
            ]);
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Atualizar perfil do usuário
     * Requirements: 9.1, 9.2, 9.3
     */
    public function updateProfile() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $input = json_decode(file_get_contents('php://input'), true);

            // Validar dados básicos
            $rules = [];
            if (isset($input['name'])) {
                $rules['name'] = 'required|min:3';
            }
            if (isset($input['email'])) {
                $rules['email'] = 'required|email';
            }

            $errors = Validator::validate($input, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            // Verificar se email já existe (se estiver sendo alterado)
            if (isset($input['email'])) {
                $existingUser = $this->userModel->findByEmail($input['email']);
                if ($existingUser && $existingUser['id'] != $payload['userId']) {
                    return Response::json(Response::error('Email já está em uso', ['email' => 'Este email já existe'], 400));
                }
            }

            // Atualizar perfil
            if ($this->userModel->updateProfile($payload['userId'], $input)) {
                // Log de sucesso
                $this->activityLogModel->logAuthEvent($payload['userId'], 'profile_update', true, [
                    'updated_fields' => array_keys($input)
                ]);

                // Enviar notificação se houve alterações significativas
                if (isset($input['email']) || isset($input['name'])) {
                    $user = $this->userModel->findById($payload['userId']);
                    $this->emailService->sendProfileUpdateNotification($user, $input);
                }

                return Response::json(Response::success('Perfil atualizado com sucesso'));
            } else {
                $this->activityLogModel->logAuthEvent($payload['userId'], 'profile_update', false, [
                    'reason' => 'database_error'
                ]);
                return Response::json(Response::error('Erro ao atualizar perfil', null, 500));
            }
        } catch (Exception $e) {
            $this->activityLogModel->logAuthEvent(null, 'profile_update', false, [
                'error' => $e->getMessage()
            ]);
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Deletar conta do usuário
     * Requirements: 9.4
     */
    public function deleteAccount() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $input = json_decode(file_get_contents('php://input'), true);

            // Validar senha para confirmação
            if (!isset($input['password'])) {
                return Response::json(Response::error('Senha é obrigatória para confirmar exclusão', null, 400));
            }

            // Buscar usuário
            $user = $this->userModel->findById($payload['userId']);

            if (!$user) {
                return Response::json(Response::error('Usuário não encontrado', null, 404));
            }

            // Verificar senha
            if (!Auth::verifyPassword($input['password'], $user['password'])) {
                return Response::json(Response::error('Senha incorreta', null, 400));
            }

            // Deletar conta (cascade irá remover dados relacionados)
            if ($this->userModel->deleteAccount($payload['userId'])) {
                // Log de exclusão
                $this->activityLogModel->logAuthEvent($payload['userId'], 'account_deletion', true);

                // Enviar confirmação de exclusão
                $this->emailService->sendAccountDeletionConfirmation($user);

                return Response::json(Response::success('Conta deletada com sucesso'));
            } else {
                return Response::json(Response::error('Erro ao deletar conta', null, 500));
            }
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Obter log de atividades do usuário
     * Requirements: 9.6
     */
    public function getActivityLog() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;

            $activities = $this->activityLogModel->getUserActivityLog($payload['userId'], $limit, $offset);

            return Response::json(Response::success('Log de atividades obtido com sucesso', $activities));
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }
}
?>
