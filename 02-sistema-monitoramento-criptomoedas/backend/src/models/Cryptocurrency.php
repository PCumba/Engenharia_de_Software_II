<?php
/**
 * Modelo de Criptomoedas
 */

class Cryptocurrency {
    private $db;
    private $pricesTable = 'crypto_prices';
    private $portfolioTable = 'portfolio';
    private $alertsTable = 'price_alerts';

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Salvar preço de criptomoeda
     */
    public function savePrice($cryptoId, $symbol, $name, $price, $marketCap, $volume24h, $percentChange24h) {
        try {
            $query = "INSERT INTO {$this->pricesTable} 
                      (crypto_id, symbol, name, price, market_cap, volume_24h, percent_change_24h, created_at) 
                      VALUES (:crypto_id, :symbol, :name, :price, :market_cap, :volume_24h, :percent_change_24h, NOW())
                      ON DUPLICATE KEY UPDATE 
                      price = :price, market_cap = :market_cap, volume_24h = :volume_24h, 
                      percent_change_24h = :percent_change_24h, updated_at = NOW()";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':crypto_id', $cryptoId);
            $stmt->bindParam(':symbol', $symbol);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':market_cap', $marketCap);
            $stmt->bindParam(':volume_24h', $volume24h);
            $stmt->bindParam(':percent_change_24h', $percentChange24h);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar preço: " . $e->getMessage());
        }
    }

    /**
     * Obter preços de criptomoedas
     */
    public function getPrices($limit = 100, $offset = 0) {
        try {
            $query = "SELECT * FROM {$this->pricesTable} 
                      ORDER BY market_cap DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter preços: " . $e->getMessage());
        }
    }

    /**
     * Adicionar criptomoeda ao portfólio
     */
    public function addToPortfolio($userId, $cryptoId, $symbol, $quantity, $purchasePrice) {
        try {
            $query = "INSERT INTO {$this->portfolioTable} 
                      (user_id, crypto_id, symbol, quantity, purchase_price, created_at) 
                      VALUES (:user_id, :crypto_id, :symbol, :quantity, :purchase_price, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':crypto_id', $cryptoId);
            $stmt->bindParam(':symbol', $symbol);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':purchase_price', $purchasePrice);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao adicionar ao portfólio: " . $e->getMessage());
        }
    }

    /**
     * Obter portfólio do usuário
     */
    public function getPortfolio($userId) {
        try {
            $query = "SELECT p.*, c.price, c.percent_change_24h FROM {$this->portfolioTable} p
                      LEFT JOIN {$this->pricesTable} c ON p.crypto_id = c.crypto_id
                      WHERE p.user_id = :user_id
                      ORDER BY p.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter portfólio: " . $e->getMessage());
        }
    }

    /**
     * Criar alerta de preço
     */
    public function createAlert($userId, $cryptoId, $symbol, $priceTarget, $alertType) {
        try {
            $query = "INSERT INTO {$this->alertsTable} 
                      (user_id, crypto_id, symbol, price_target, alert_type, is_active, created_at) 
                      VALUES (:user_id, :crypto_id, :symbol, :price_target, :alert_type, 1, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':crypto_id', $cryptoId);
            $stmt->bindParam(':symbol', $symbol);
            $stmt->bindParam(':price_target', $priceTarget);
            $stmt->bindParam(':alert_type', $alertType);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar alerta: " . $e->getMessage());
        }
    }

    /**
     * Obter alertas do usuário
     */
    public function getAlerts($userId) {
        try {
            $query = "SELECT * FROM {$this->alertsTable} 
                      WHERE user_id = :user_id AND is_active = 1
                      ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter alertas: " . $e->getMessage());
        }
    }

    /**
     * Remover do portfólio
     */
    public function removeFromPortfolio($userId, $portfolioId) {
        try {
            $query = "DELETE FROM {$this->portfolioTable} WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $portfolioId);
            $stmt->bindParam(':user_id', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao remover do portfólio: " . $e->getMessage());
        }
    }

    /**
     * Desativar alerta
     */
    public function disableAlert($userId, $alertId) {
        try {
            $query = "UPDATE {$this->alertsTable} SET is_active = 0 WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $alertId);
            $stmt->bindParam(':user_id', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao desativar alerta: " . $e->getMessage());
        }
    }
}
?>
