<?php
/**
 * Service - Menu
 */

class MenuService {
    private $menuItemModel;

    public function __construct($database) {
        $this->menuItemModel = new MenuItem($database);
    }

    public function getMenuByRestaurant($restaurantId) {
        $items = $this->menuItemModel->getByRestaurant($restaurantId);
        
        $grouped = [];
        foreach ($items as $item) {
            $category = $item['category'] ?: 'Outros';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $item;
        }

        return $grouped;
    }

    public function getItemsByCategory($restaurantId, $category) {
        return $this->menuItemModel->getByCategory($restaurantId, $category);
    }

    public function searchItems($restaurantId, $query) {
        return $this->menuItemModel->search($restaurantId, $query);
    }

    public function getPopularItems($restaurantId) {
        return $this->menuItemModel->getByRestaurant($restaurantId);
    }
}
?>
