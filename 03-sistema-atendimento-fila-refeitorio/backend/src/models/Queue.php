<?php
/**
 * Modelo de Fila de Atendimento
 */

class Queue {
    private $db;
    private $ticketsTable = 'tickets';
    private $servicesTable = 'services';
    private $queueHistoryTable = 'queue_history';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Criar novo ticket
     */
    public function createTicket($userId, $serviceId) {
        try {
            // Gerar número do ticket (sequencial por dia)
            $today = date('Y-m-d');
            $query = "SELECT COUNT(*) as count FROM {$this->ticketsTable} 
                      WHERE service_id = :service_id AND DATE(created_at) = :today";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':service_id', $serviceId);
            $stmt->bindParam(':today', $today);
            $stmt->execute();
            
            $result = $stmt->fetch();
            $ticketNumber = ($result['count'] + 1);

            // Inserir ticket
            $query = "INSERT INTO {$this->ticketsTable} 
                      (user_id, service_id, ticket_number, status, created_at) 
                      VALUES (:user_id, :service_id, :ticket_number, :status, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':service_id', $serviceId);
            $stmt->bindParam(':ticket_number', $ticketNumber);
            $status = 'waiting';
            $stmt->bindParam(':status', $status);

            $stmt->execute();
            
            return [
                'id' => $this->db->lastInsertId(),
                'ticketNumber' => $ticketNumber,
                'status' => 'waiting'
            ];
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar ticket: " . $e->getMessage());
        }
    }

    /**
     * Obter tickets em fila
     */
    public function getQueueByService($serviceId) {
        try {
            $query = "SELECT t.*, u.name as user_name FROM {$this->ticketsTable} t
                      LEFT JOIN users u ON t.user_id = u.id
                      WHERE t.service_id = :service_id AND t.status IN ('waiting', 'calling')
                      ORDER BY t.created_at ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':service_id', $serviceId);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter fila: " . $e->getMessage());
        }
    }

    /**
     * Chamar próximo ticket
     */
    public function callNextTicket($serviceId) {
        try {
            // Encontrar próximo ticket em waiting
            $query = "SELECT * FROM {$this->ticketsTable} 
                      WHERE service_id = :service_id AND status = 'waiting'
                      ORDER BY created_at ASC LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':service_id', $serviceId);
            $stmt->execute();

            $ticket = $stmt->fetch();

            if (!$ticket) {
                return null;
            }

            // Atualizar status para calling
            $updateQuery = "UPDATE {$this->ticketsTable} SET status = :status, called_at = NOW() 
                            WHERE id = :id";
            
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':id', $ticket['id']);
            $status = 'calling';
            $updateStmt->bindParam(':status', $status);
            $updateStmt->execute();

            return $ticket;
        } catch (PDOException $e) {
            throw new Exception("Erro ao chamar ticket: " . $e->getMessage());
        }
    }

    /**
     * Completar atendimento
     */
    public function completeTicket($ticketId) {
        try {
            $query = "UPDATE {$this->ticketsTable} 
                      SET status = :status, completed_at = NOW() 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $ticketId);
            $status = 'completed';
            $stmt->bindParam(':status', $status);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao completar ticket: " . $e->getMessage());
        }
    }

    /**
     * Cancelar ticket
     */
    public function cancelTicket($ticketId) {
        try {
            $query = "UPDATE {$this->ticketsTable} 
                      SET status = :status, cancelled_at = NOW() 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $ticketId);
            $status = 'cancelled';
            $stmt->bindParam(':status', $status);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao cancelar ticket: " . $e->getMessage());
        }
    }

    /**
     * Obter posição do usuário na fila
     */
    public function getUserPosition($userId, $serviceId) {
        try {
            $query = "SELECT COUNT(*) as position FROM {$this->ticketsTable} 
                      WHERE service_id = :service_id 
                      AND status = 'waiting'
                      AND created_at < (SELECT created_at FROM {$this->ticketsTable} 
                                       WHERE user_id = :user_id AND service_id = :service_id 
                                       LIMIT 1)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':service_id', $serviceId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $result = $stmt->fetch();
            return $result['position'] + 1;
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter posição: " . $e->getMessage());
        }
    }

    /**
     * Obter ticket ativo do usuário
     */
    public function getUserActiveTicket($userId) {
        try {
            $query = "SELECT t.*, s.name as service_name FROM {$this->ticketsTable} t
                      LEFT JOIN {$this->servicesTable} s ON t.service_id = s.id
                      WHERE t.user_id = :user_id AND t.status IN ('waiting', 'calling')
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter ticket: " . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas da fila
     */
    public function getQueueStats($serviceId) {
        try {
            $query = "SELECT 
                      (SELECT COUNT(*) FROM {$this->ticketsTable} 
                       WHERE service_id = :service_id AND status = 'waiting') as waiting_count,
                      (SELECT COUNT(*) FROM {$this->ticketsTable} 
                       WHERE service_id = :service_id AND status = 'calling') as calling_count,
                      (SELECT COUNT(*) FROM {$this->ticketsTable} 
                       WHERE service_id = :service_id AND status = 'completed' AND DATE(completed_at) = CURDATE()) as completed_today";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':service_id', $serviceId);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter estatísticas: " . $e->getMessage());
        }
    }
}
?>
