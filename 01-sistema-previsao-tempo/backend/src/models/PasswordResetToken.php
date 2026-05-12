<?php
/**
 * Modelo de Token de Redefinição de Senha
 * Requirements: 8.1, 8.2, 8.3
 */

class PasswordResetToken {
    private $db;
    private $table = 'password_reset_tokens';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Criar novo token de redefinição de senha
     * @param int $userId ID do usuário
     * @param string $token Token criptograficamente seguro
     * @param string $expiresAt Data de expiração (formato: Y-m-d H:i:s)
     * @return bool
     */
    public function create($userId, $token, $expiresAt) {
        try {
            $query = "INSERT INTO {$this->table} (user_id, token, expires_at, created_at) 
                      VALUES (:user_id, :token, :expires_at, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expiresAt, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar token de redefinição: " . $e->getMessage());
        }
    }

    /**
     * Buscar token por valor
     * @param string $token Token a ser buscado
     * @return array|false Dados do token ou false se não encontrado
     */
    public function findByToken($token) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE token = :token";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar token: " . $e->getMessage());
        }
    }

    /**
     * Invalidar token (marcar como usado)
     * @param string $token Token a ser invalidado
     * @return bool
     */
    public function invalidateToken($token) {
        try {
            $query = "UPDATE {$this->table} SET used_at = CURRENT_TIMESTAMP WHERE token = :token";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao invalidar token: " . $e->getMessage());
        }
    }

    /**
     * Limpar tokens expirados
     * @return int Número de tokens removidos
     */
    public function cleanupExpiredTokens() {
        try {
            $query = "DELETE FROM {$this->table} WHERE expires_at < CURRENT_TIMESTAMP";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Erro ao limpar tokens expirados: " . $e->getMessage());
        }
    }

    /**
     * Verificar se token é válido
     * @param string $token Token a ser verificado
     * @return bool
     */
    public function isTokenValid($token) {
        try {
            $tokenData = $this->findByToken($token);
            
            if (!$tokenData) {
                return false;
            }

            // Verificar se token já foi usado
            if ($tokenData['used_at'] !== null) {
                return false;
            }

            // Verificar se token não expirou
            $now = new DateTime();
            $expiresAt = new DateTime($tokenData['expires_at']);
            
            return $now <= $expiresAt;
        } catch (Exception $e) {
            throw new Exception("Erro ao validar token: " . $e->getMessage());
        }
    }

    /**
     * Gerar token criptograficamente seguro
     * @return string Token de 64 caracteres
     */
    public static function generateSecureToken() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Calcular data de expiração (1 hora a partir de agora)
     * @return string Data de expiração no formato Y-m-d H:i:s
     */
    public static function calculateExpirationTime() {
        $expirationTime = new DateTime();
        $expirationTime->add(new DateInterval('PT1H')); // Adicionar 1 hora
        return $expirationTime->format('Y-m-d H:i:s');
    }

    /**
     * Invalidar todos os tokens de um usuário
     * @param int $userId ID do usuário
     * @return bool
     */
    public function invalidateUserTokens($userId) {
        try {
            $query = "UPDATE {$this->table} SET used_at = CURRENT_TIMESTAMP 
                      WHERE user_id = :user_id AND used_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao invalidar tokens do usuário: " . $e->getMessage());
        }
    }
}
?>