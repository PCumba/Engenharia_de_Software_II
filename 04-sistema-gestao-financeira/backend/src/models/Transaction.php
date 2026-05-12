<?php
/**
 * Modelo de Transações
 */

class Transaction {
    private $db;
    private $table = 'transactions';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    public function create($userId, $categoryId, $description, $amount, $type, $date = null) {
        try {
            $date = $date ?: date('Y-m-d');
            
            $query = "INSERT INTO {$this->table} 
                      (user_id, category_id, description, amount, type, transaction_date, created_at) 
                      VALUES (:user_id, :category_id, :description, :amount, :type, :date, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':category_id', $categoryId);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':date', $date);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar transação: " . $e->getMessage());
        }
    }

    public function getByUser($userId, $limit = 100, $offset = 0) {
        try {
            $query = "SELECT t.*, c.name as category_name, c.color 
                      FROM {$this->table} t
                      LEFT JOIN categories c ON t.category_id = c.id
                      WHERE t.user_id = :user_id
                      ORDER BY t.transaction_date DESC, t.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar transações: " . $e->getMessage());
        }
    }

    public function getByUserAndPeriod($userId, $startDate, $endDate) {
        try {
            $query = "SELECT t.*, c.name as category_name, c.color 
                      FROM {$this->table} t
                      LEFT JOIN categories c ON t.category_id = c.id
                      WHERE t.user_id = :user_id
                      AND t.transaction_date BETWEEN :start_date AND :end_date
                      ORDER BY t.transaction_date DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar transações: " . $e->getMessage());
        }
    }

    public function findById($id, $userId) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter transação: " . $e->getMessage());
        }
    }

    public function update($id, $userId, $categoryId, $description, $amount, $date) {
        try {
            $query = "UPDATE {$this->table} 
                      SET category_id = :category_id, description = :description, 
                          amount = :amount, transaction_date = :date
                      WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':category_id', $categoryId);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar transação: " . $e->getMessage());
        }
    }

    public function delete($id, $userId) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar transação: " . $e->getMessage());
        }
    }

    public function getBalance($userId) {
        try {
            $query = "SELECT 
                      SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                      SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses,
                      SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance
                      FROM {$this->table}
                      WHERE user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erro ao calcular saldo: " . $e->getMessage());
        }
    }
}
?>
