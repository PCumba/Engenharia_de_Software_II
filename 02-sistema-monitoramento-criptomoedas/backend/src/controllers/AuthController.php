<?php
/**
 * Controller de Autenticação
 */

class AuthController {
    private $userModel;

    public function __construct($database) {
        $this->userModel = new User($database);
    }

    /**
     * Registar novo utilizador
     */
    public function register() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:6',
                'name' => 'required|min:3'
            ];

            $errors = Validator::validate($data, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            // Verificar se email já existe
            if ($this->userModel->emailExists($data['email'])) {
                return Response::error('Email já registado', ['email' => ['Email já existe']], 409);
            }

            // Hash da password
            $hashedPassword = Auth::hashPassword($data['password']);

            // Criar utilizador
            $this->userModel->create($data['email'], $hashedPassword, $data['name']);

            return Response::success('Utilizador registado com sucesso', null, 201);
        } catch (Exception $e) {
            return Response::error('Erro ao registar', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fazer login
     */
    public function login() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'email' => 'required|email',
                'password' => 'required'
            ];

            $errors = Validator::validate($data, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            $user = $this->userModel->findByEmail($data['email']);

            if (!$user || !Auth::verifyPassword($data['password'], $user['password'])) {
                return Response::error('Email ou password inválidos', null, 401);
            }

            $token = Auth::generateToken($user['id'], $user['email']);

            return Response::success('Login realizado com sucesso', [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'language' => $user['language'],
                    'theme' => $user['theme']
                ]
            ]);
        } catch (Exception $e) {
            return Response::error('Erro ao fazer login', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter dados do utilizador autenticado
     */
    public function me() {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $user = $this->userModel->findById($token['userId']);

            if (!$user) {
                return Response::error('Utilizador não encontrado', null, 404);
            }

            return Response::success('Dados do utilizador', $user);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fazer logout (apenas confirma no frontend)
     */
    public function logout() {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            return Response::success('Logout realizado com sucesso');
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
