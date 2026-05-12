<?php
/**
 * Modelo de Categorias
 */

class Category {
    private $db;
    private $table = 'categories';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    public function getByUser($userId) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY name ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar categorias: " . $e->getMessage());
        }
    }

    public function create($userId, $name, $type, $color = '#667eea') {
        try {
            $query = "INSERT INTO {$this->table} (user_id, name, type, color, created_at) 
                      VALUES (:user_id, :name, :type, :color, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':color', $color);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar categoria: " . $e->getMessage());
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
            throw new Exception("Erro ao obter categoria: " . $e->getMessage());
        }
    }

    public function update($id, $userId, $name, $color) {
        try {
            $query = "UPDATE {$this->table} SET name = :name, color = :color 
                      WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar categoria: " . $e->getMessage());
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
            throw new Exception("Erro ao deletar categoria: " . $e->getMessage());
        }
    }
}
?>
