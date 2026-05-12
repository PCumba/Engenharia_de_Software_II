<?php
/**
 * Modelo de Usuário
 */

class User {
    private $db;
    private $table = 'users';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Criar novo usuário
     */
    public function create($email, $password, $name) {
        try {
            $query = "INSERT INTO {$this->table} (email, password, name, language, theme, created_at) 
                      VALUES (:email, :password, :name, :language, :theme, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':name', $name);

            $language = 'pt';
            $theme = 'light';
            $stmt->bindParam(':language', $language);
            $stmt->bindParam(':theme', $theme);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar usuário: " . $e->getMessage());
        }
    }

    /**
     * Buscar usuário por email
     */
    public function findByEmail($email) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE email = :email";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuário: " . $e->getMessage());
        }
    }

    /**
     * Buscar usuário por ID
     */
    public function findById($id) {
        try {
            $query = "SELECT id, email, name, language, theme, created_at FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuário: " . $e->getMessage());
        }
    }

    /**
     * Atualizar preferências do usuário
     */
    public function updatePreferences($id, $language, $theme) {
        try {
            $query = "UPDATE {$this->table} SET language = :language, theme = :theme, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':language', $language);
            $stmt->bindParam(':theme', $theme);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar preferências: " . $e->getMessage());
        }
    }

    /**
     * Atualizar senha
     */
    public function updatePassword($id, $password) {
        try {
            $query = "UPDATE {$this->table} SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':password', $password);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar senha: " . $e->getMessage());
        }
    }

    /**
     * Verificar se email existe
     */
    public function emailExists($email) {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar email: " . $e->getMessage());
        }
    }

    /**
     * Incrementar tentativas de login falhadas
     * Requirements: 1.7
     * @param int $userId ID do usuário
     * @return bool
     */
    public function incrementFailedAttempts($userId) {
        try {
            $query = "UPDATE {$this->table} 
                      SET failed_login_attempts = failed_login_attempts + 1,
                          updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao incrementar tentativas falhadas: " . $e->getMessage());
        }
    }

    /**
     * Bloquear conta por 15 minutos após 5 tentativas falhadas
     * Requirements: 1.7
     * @param int $userId ID do usuário
     * @return bool
     */
    public function lockAccount($userId) {
        try {
            $lockUntil = new DateTime();
            $lockUntil->add(new DateInterval('PT15M')); // 15 minutos
            
            $query = "UPDATE {$this->table} 
                      SET locked_until = :locked_until,
                          updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':locked_until', $lockUntil->format('Y-m-d H:i:s'));

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao bloquear conta: " . $e->getMessage());
        }
    }

    /**
     * Resetar tentativas de login falhadas
     * Requirements: 1.7
     * @param int $userId ID do usuário
     * @return bool
     */
    public function resetFailedAttempts($userId) {
        try {
            $query = "UPDATE {$this->table} 
                      SET failed_login_attempts = 0,
                          locked_until = NULL,
                          updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao resetar tentativas falhadas: " . $e->getMessage());
        }
    }

    /**
     * Verificar se conta está bloqueada
     * Requirements: 1.7
     * @param int $userId ID do usuário
     * @return bool
     */
    public function isAccountLocked($userId) {
        try {
            $query = "SELECT locked_until FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch();
            
            if (!$result || !$result['locked_until']) {
                return false;
            }

            $now = new DateTime();
            $lockedUntil = new DateTime($result['locked_until']);
            
            // Se o tempo de bloqueio passou, desbloquear automaticamente
            if ($now > $lockedUntil) {
                $this->resetFailedAttempts($userId);
                return false;
            }

            return true;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar bloqueio da conta: " . $e->getMessage());
        }
    }

    /**
     * Obter número de tentativas falhadas
     * Requirements: 1.7
     * @param int $userId ID do usuário
     * @return int
     */
    public function getFailedAttempts($userId) {
        try {
            $query = "SELECT failed_login_attempts FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch();
            return $result ? (int)$result['failed_login_attempts'] : 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter tentativas falhadas: " . $e->getMessage());
        }
    }

    /**
     * Atualizar perfil do usuário
     * Requirements: 9.1, 9.2
     * @param int $id ID do usuário
     * @param array $data Dados a serem atualizados
     * @return bool
     */
    public function updateProfile($id, $data) {
        try {
            $allowedFields = ['name', 'email', 'language', 'theme', 'notification_preferences'];
            $updateFields = [];
            $params = [':id' => $id];

            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $field === 'notification_preferences' ? json_encode($value) : $value;
                }
            }

            if (empty($updateFields)) {
                return false;
            }

            $query = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . 
                     ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar perfil: " . $e->getMessage());
        }
    }

    /**
     * Deletar conta do usuário
     * Requirements: 9.4
     * @param int $id ID do usuário
     * @return bool
     */
    public function deleteAccount($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar conta: " . $e->getMessage());
        }
    }
}
?>
