<?php
/**
 * Service - Restaurante
 */

class RestaurantService {
    private $restaurantModel;
    private $menuItemModel;
    private $reviewModel;

    public function __construct($database) {
        $this->restaurantModel = new Restaurant($database);
        $this->menuItemModel = new MenuItem($database);
        $this->reviewModel = new Review($database);
    }

    public function getAllRestaurants($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $restaurants = $this->restaurantModel->getAll($perPage, $offset);
        $total = $this->restaurantModel->getTotalCount();

        return [
            'restaurants' => $restaurants,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ];
    }

    public function getRestaurantDetails($restaurantId) {
        $restaurant = $this->restaurantModel->findById($restaurantId);
        if (!$restaurant) return null;

        $restaurant['menu'] = $this->menuItemModel->getByRestaurant($restaurantId);
        $restaurant['reviews'] = $this->reviewModel->getByRestaurant($restaurantId, 10);
        $restaurant['stats'] = $this->reviewModel->getStats($restaurantId);

        return $restaurant;
    }

    public function searchRestaurants($cuisineType = null, $isOpen = null) {
        return $this->restaurantModel->getByFilters($cuisineType, $isOpen);
    }
}
?>
