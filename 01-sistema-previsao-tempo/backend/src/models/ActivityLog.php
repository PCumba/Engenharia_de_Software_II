<?php
/**
 * Modelo de Log de Atividades
 * Requirements: 1.8, 8.7, 9.6
 */

class ActivityLog {
    private $db;
    private $table = 'activity_logs';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Registrar atividade do usuário
     * @param int $userId ID do usuário (pode ser null para atividades anônimas)
     * @param string $action Ação realizada
     * @param string $description Descrição da atividade
     * @param array $metadata Metadados adicionais
     * @param string $ipAddress Endereço IP
     * @param string $userAgent User Agent do navegador
     * @return bool
     */
    public function log($userId, $action, $description, $metadata = [], $ipAddress = null, $userAgent = null) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (user_id, action, description, metadata, ip_address, user_agent, created_at) 
                      VALUES (:user_id, :action, :description, :metadata, :ip_address, :user_agent, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':action', $action, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':metadata', json_encode($metadata), PDO::PARAM_STR);
            $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao registrar atividade: " . $e->getMessage());
        }
    }

    /**
     * Registrar evento de autenticação
     * Requirements: 1.8, 8.7
     * @param int $userId ID do usuário (pode ser null para tentativas falhadas)
     * @param string $event Tipo de evento (login, logout, password_reset, failed_login, etc.)
     * @param bool $success Se o evento foi bem-sucedido
     * @param array $additionalData Dados adicionais do evento
     * @return bool
     */
    public function logAuthEvent($userId, $event, $success, $additionalData = []) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $metadata = array_merge([
            'success' => $success,
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => 'authentication'
        ], $additionalData);

        $description = $this->getAuthEventDescription($event, $success);

        return $this->log($userId, $event, $description, $metadata, $ipAddress, $userAgent);
    }

    /**
     * Obter histórico de atividades do usuário
     * Requirements: 9.6
     * @param int $userId ID do usuário
     * @param int $limit Limite de registros
     * @param int $offset Offset para paginação
     * @return array
     */
    public function getUserActivityLog($userId, $limit = 50, $offset = 0) {
        try {
            $query = "SELECT * FROM {$this->table} 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter log de atividades: " . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas de eventos de autenticação
     * Requirements: 1.8, 8.7
     * @param int $userId ID do usuário (opcional)
     * @param string $period Período (day, week, month)
     * @return array
     */
    public function getAuthStats($userId = null, $period = 'day') {
        try {
            $dateCondition = $this->getDateCondition($period);
            $userCondition = $userId ? "AND user_id = :user_id" : "";

            $query = "SELECT action, 
                             COUNT(*) as total,
                             SUM(CASE WHEN JSON_EXTRACT(metadata, '$.success') = true THEN 1 ELSE 0 END) as successful,
                             SUM(CASE WHEN JSON_EXTRACT(metadata, '$.success') = false THEN 1 ELSE 0 END) as failed
                      FROM {$this->table} 
                      WHERE JSON_EXTRACT(metadata, '$.event_type') = 'authentication' 
                      AND created_at >= $dateCondition
                      $userCondition
                      GROUP BY action";
            
            $stmt = $this->db->prepare($query);
            
            if ($userId) {
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            }
            
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter estatísticas de autenticação: " . $e->getMessage());
        }
    }

    /**
     * Limpar logs antigos
     * @param int $daysToKeep Número de dias para manter os logs
     * @return int Número de registros removidos
     */
    public function cleanupOldLogs($daysToKeep = 90) {
        try {
            $query = "DELETE FROM {$this->table} 
                      WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':days', $daysToKeep, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Erro ao limpar logs antigos: " . $e->getMessage());
        }
    }

    /**
     * Obter descrição do evento de autenticação
     * @param string $event Tipo de evento
     * @param bool $success Se foi bem-sucedido
     * @return string
     */
    private function getAuthEventDescription($event, $success) {
        $descriptions = [
            'login' => $success ? 'Login realizado com sucesso' : 'Tentativa de login falhada',
            'logout' => 'Logout realizado',
            'register' => $success ? 'Registro realizado com sucesso' : 'Tentativa de registro falhada',
            'password_reset_request' => 'Solicitação de redefinição de senha',
            'password_reset' => $success ? 'Senha redefinida com sucesso' : 'Tentativa de redefinição de senha falhada',
            'password_change' => $success ? 'Senha alterada com sucesso' : 'Tentativa de alteração de senha falhada',
            'account_locked' => 'Conta bloqueada por tentativas excessivas',
            'profile_update' => $success ? 'Perfil atualizado com sucesso' : 'Tentativa de atualização de perfil falhada',
            'account_deletion' => 'Conta deletada'
        ];

        return $descriptions[$event] ?? "Evento de autenticação: $event";
    }

    /**
     * Obter condição de data baseada no período
     * @param string $period Período (day, week, month)
     * @return string
     */
    private function getDateCondition($period) {
        switch ($period) {
            case 'week':
                return 'DATE_SUB(NOW(), INTERVAL 7 DAY)';
            case 'month':
                return 'DATE_SUB(NOW(), INTERVAL 30 DAY)';
            case 'day':
            default:
                return 'DATE_SUB(NOW(), INTERVAL 1 DAY)';
        }
    }
}
?>