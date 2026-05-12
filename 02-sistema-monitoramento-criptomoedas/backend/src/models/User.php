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

    public function create($email, $password, $name) {
        try {
            $query = "INSERT INTO {$this->table} (email, password, name, language, theme, created_at) 
                      VALUES (:email, :password, :name, :language, :theme, NOW())";
            
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
}
?>
