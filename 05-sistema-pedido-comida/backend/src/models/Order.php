<?php
/**
 * Model - Pedido
 */

class Order {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function create($userId, $restaurantId, $totalPrice, $deliveryAddress, $deliveryNotes) {
        $query = 'INSERT INTO orders (user_id, restaurant_id, total_price, delivery_address, delivery_notes, status) 
                  VALUES (?, ?, ?, ?, ?, ?)';
        $this->database->execute($query, [$userId, $restaurantId, $totalPrice, $deliveryAddress, $deliveryNotes, 'pending']);
        return $this->database->lastInsertId();
    }

    public function getByUser($userId, $limit = 50) {
        $query = 'SELECT o.*, r.name as restaurant_name FROM orders o 
                  JOIN restaurants r ON o.restaurant_id = r.id 
                  WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT ?';
        return $this->database->query($query, [$userId, $limit]);
    }

    public function findById($id, $userId = null) {
        $query = 'SELECT o.*, r.name as restaurant_name FROM orders o 
                  JOIN restaurants r ON o.restaurant_id = r.id 
                  WHERE o.id = ?';
        $params = [$id];

        if ($userId) {
            $query .= ' AND o.user_id = ?';
            $params[] = $userId;
        }

        $result = $this->database->query($query, $params);
        return $result ? $result[0] : null;
    }

    public function updateStatus($id, $status) {
        $query = 'UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?';
        $this->database->execute($query, [$status, $id]);
    }

    public function getStats($restaurantId) {
        $query = 'SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_price) as total_revenue,
                    AVG(total_price) as avg_order_value
                  FROM orders WHERE restaurant_id = ?';
        $result = $this->database->query($query, [$restaurantId]);
        return $result[0];
    }
}
?>
