<?php
/**
 * Model - Restaurante
 */

class Restaurant {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function getAll($limit = 20, $offset = 0) {
        $query = 'SELECT id, name, cuisine_type, description, image_url, rating, delivery_fee, delivery_time, is_open FROM restaurants LIMIT ? OFFSET ?';
        $result = $this->database->query($query, [$limit, $offset]);
        return $result ?: [];
    }

    public function getTotalCount() {
        $query = 'SELECT COUNT(*) as count FROM restaurants';
        $result = $this->database->query($query);
        return $result[0]['count'];
    }

    public function findById($id) {
        $query = 'SELECT * FROM restaurants WHERE id = ?';
        $result = $this->database->query($query, [$id]);
        return $result ? $result[0] : null;
    }

    public function getByFilters($cuisineType = null, $isOpen = null) {
        $query = 'SELECT * FROM restaurants WHERE 1=1';
        $params = [];

        if ($cuisineType) {
            $query .= ' AND cuisine_type = ?';
            $params[] = $cuisineType;
        }

        if ($isOpen !== null) {
            $query .= ' AND is_open = ?';
            $params[] = $isOpen;
        }

        $query .= ' ORDER BY rating DESC';
        return $this->database->query($query, $params);
    }

    public function updateRating($restaurantId) {
        $query = 'UPDATE restaurants SET rating = (
                    SELECT AVG(rating) FROM reviews WHERE restaurant_id = ?
                  ) WHERE id = ?';
        $this->database->execute($query, [$restaurantId, $restaurantId]);
    }
}
?>
