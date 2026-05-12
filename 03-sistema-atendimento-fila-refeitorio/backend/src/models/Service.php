<?php
/**
 * Modelo de Serviços
 */

class Service {
    private $db;
    private $table = 'services';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Listar todos os serviços
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar serviços: " . $e->getMessage());
        }
    }

    /**
     * Obter serviço por ID
     */
    public function findById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id AND is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter serviço: " . $e->getMessage());
        }
    }

    /**
     * Criar novo serviço
     */
    public function create($name, $description = '') {
        try {
            $query = "INSERT INTO {$this->table} (name, description, is_active, created_at) 
                      VALUES (:name, :description, 1, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar serviço: " . $e->getMessage());
        }
    }
}
?>
