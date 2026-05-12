<?php
/**
 * Model - Avaliação (Review)
 */

class Review {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function create($orderId, $restaurantId, $userId, $rating, $comment) {
        $query = 'INSERT INTO reviews (order_id, restaurant_id, user_id, rating, comment) VALUES (?, ?, ?, ?, ?)';
        $this->database->execute($query, [$orderId, $restaurantId, $userId, $rating, $comment]);
        return $this->database->lastInsertId();
    }

    public function getByRestaurant($restaurantId, $limit = 20) {
        $query = 'SELECT r.*, u.name as user_name FROM reviews r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.restaurant_id = ? ORDER BY r.created_at DESC LIMIT ?';
        return $this->database->query($query, [$restaurantId, $limit]);
    }

    public function checkIfReviewed($orderId) {
        $query = 'SELECT COUNT(*) as count FROM reviews WHERE order_id = ?';
        $result = $this->database->query($query, [$orderId]);
        return $result[0]['count'] > 0;
    }

    public function getStats($restaurantId) {
        $query = 'SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as avg_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                  FROM reviews WHERE restaurant_id = ?';
        $result = $this->database->query($query, [$restaurantId]);
        return $result[0];
    }
}
?>
