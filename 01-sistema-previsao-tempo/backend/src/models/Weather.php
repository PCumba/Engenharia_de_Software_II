<?php
/**
 * Modelo de Previsão do Tempo
 */

class Weather {
    private $db;
    private $table = 'weather_searches';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Salvar busca de previsão do tempo
     */
    public function saveSearch($userId, $city, $country, $data) {
        try {
            $query = "INSERT INTO {$this->table} (user_id, city, country, weather_data, created_at) 
                      VALUES (:user_id, :city, :country, :weather_data, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':country', $country);
            $weatherData = json_encode($data);
            $stmt->bindParam(':weather_data', $weatherData);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar busca: " . $e->getMessage());
        }
    }

    /**
     * Obter buscas recentes do usuário
     */
    public function getSearchHistory($userId, $limit = 10) {
        try {
            $query = "SELECT id, city, country, weather_data, created_at FROM {$this->table} 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll();
            
            // Decodificar JSON
            foreach ($results as &$result) {
                $result['weather_data'] = json_decode($result['weather_data'], true);
            }

            return $results;
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter histórico: " . $e->getMessage());
        }
    }

    /**
     * Adicionar localização favorita
     */
    public function addFavorite($userId, $city, $country) {
        try {
            $query = "INSERT INTO favorites (user_id, city, country, created_at) 
                      VALUES (:user_id, :city, :country, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':country', $country);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao adicionar favorito: " . $e->getMessage());
        }
    }

    /**
     * Obter localizações favoritas
     */
    public function getFavorites($userId) {
        try {
            $query = "SELECT id, city, country FROM favorites WHERE user_id = :user_id ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter favoritos: " . $e->getMessage());
        }
    }

    /**
     * Remover favorito
     */
    public function removeFavorite($userId, $favoriteId) {
        try {
            $query = "DELETE FROM favorites WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $favoriteId);
            $stmt->bindParam(':user_id', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao remover favorito: " . $e->getMessage());
        }
    }
}
?>
