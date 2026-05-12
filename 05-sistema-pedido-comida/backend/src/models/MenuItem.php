<?php
/**
 * Model - Item do Menu
 */

class MenuItem {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function getByRestaurant($restaurantId) {
        $query = 'SELECT * FROM menu_items WHERE restaurant_id = ? AND is_available = 1 ORDER BY category, name';
        return $this->database->query($query, [$restaurantId]);
    }

    public function findById($id) {
        $query = 'SELECT * FROM menu_items WHERE id = ?';
        $result = $this->database->query($query, [$id]);
        return $result ? $result[0] : null;
    }

    public function create($restaurantId, $name, $description, $price, $category, $imageUrl) {
        $query = 'INSERT INTO menu_items (restaurant_id, name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?, ?)';
        $this->database->execute($query, [$restaurantId, $name, $description, $price, $category, $imageUrl]);
        return $this->database->lastInsertId();
    }

    public function getByCategory($restaurantId, $category) {
        $query = 'SELECT * FROM menu_items WHERE restaurant_id = ? AND category = ? AND is_available = 1 ORDER BY name';
        return $this->database->query($query, [$restaurantId, $category]);
    }

    public function search($restaurantId, $query) {
        $searchQuery = 'SELECT * FROM menu_items WHERE restaurant_id = ? AND (name LIKE ? OR description LIKE ?) AND is_available = 1';
        $searchTerm = "%$query%";
        return $this->database->query($searchQuery, [$restaurantId, $searchTerm, $searchTerm]);
    }
}
?>
