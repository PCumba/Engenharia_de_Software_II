<?php
/**
 * Modelo de Orçamentos
 */

class Budget {
    private $db;
    private $table = 'budgets';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    public function create($userId, $categoryId, $limitAmount, $month, $year) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (user_id, category_id, limit_amount, month, year, created_at) 
                      VALUES (:user_id, :category_id, :limit_amount, :month, :year, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':category_id', $categoryId);
            $stmt->bindParam(':limit_amount', $limitAmount);
            $stmt->bindParam(':month', $month);
            $stmt->bindParam(':year', $year);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar orçamento: " . $e->getMessage());
        }
    }

    public function getByUserAndMonth($userId, $month, $year) {
        try {
            $query = "SELECT b.*, c.name as category_name, c.color,
                      COALESCE(SUM(t.amount), 0) as spent
                      FROM {$this->table} b
                      LEFT JOIN categories c ON b.category_id = c.id
                      LEFT JOIN transactions t ON t.category_id = b.category_id 
                        AND t.user_id = b.user_id
                        AND t.type = 'expense'
                        AND MONTH(t.transaction_date) = :month
                        AND YEAR(t.transaction_date) = :year
                      WHERE b.user_id = :user_id
                      AND b.month = :month
                      AND b.year = :year
                      GROUP BY b.id, c.id
                      ORDER BY c.name";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar orçamentos: " . $e->getMessage());
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
            throw new Exception("Erro ao obter orçamento: " . $e->getMessage());
        }
    }

    public function update($id, $userId, $limitAmount) {
        try {
            $query = "UPDATE {$this->table} SET limit_amount = :limit_amount 
                      WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':limit_amount', $limitAmount);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar orçamento: " . $e->getMessage());
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
            throw new Exception("Erro ao deletar orçamento: " . $e->getMessage());
        }
    }
}
?>
