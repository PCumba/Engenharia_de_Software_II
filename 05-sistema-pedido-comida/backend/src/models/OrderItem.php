<?php
/**
 * Model - Item do Pedido
 */

class OrderItem {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function create($orderId, $menuItemId, $quantity, $price) {
        $query = 'INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)';
        $this->database->execute($query, [$orderId, $menuItemId, $quantity, $price]);
        return $this->database->lastInsertId();
    }

    public function getByOrder($orderId) {
        $query = 'SELECT oi.*, mi.name, mi.image_url FROM order_items oi 
                  JOIN menu_items mi ON oi.menu_item_id = mi.id 
                  WHERE oi.order_id = ? ORDER BY oi.created_at ASC';
        return $this->database->query($query, [$orderId]);
    }

    public function getPopularItems($restaurantId, $days = 30) {
        $query = 'SELECT mi.id, mi.name, COUNT(*) as order_count, SUM(oi.quantity) as total_quantity
                  FROM order_items oi
                  JOIN menu_items mi ON oi.menu_item_id = mi.id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE mi.restaurant_id = ? AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                  GROUP BY mi.id ORDER BY order_count DESC LIMIT 10';
        return $this->database->query($query, [$restaurantId, $days]);
    }
}
?>
