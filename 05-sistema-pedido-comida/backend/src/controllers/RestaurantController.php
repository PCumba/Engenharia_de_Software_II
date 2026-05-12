<?php
/**
 * Controller - Restaurante
 */

class RestaurantController {
    private $restaurantService;

    public function __construct($database) {
        $this->restaurantService = new RestaurantService($database);
    }

    public function getAll() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 20;

            $result = $this->restaurantService->getAllRestaurants($page, $perPage);
            return Response::paginated($result['restaurants'], $result['total'], $page, $perPage);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getById($restaurantId) {
        try {
            $restaurant = $this->restaurantService->getRestaurantDetails($restaurantId);
            if (!$restaurant) {
                return Response::error('Restaurante não encontrado', null, 404);
            }

            return Response::success('Detalhes do restaurante', $restaurant);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function search() {
        try {
            $cuisineType = $_GET['cuisine'] ?? null;
            $isOpen = isset($_GET['open']) ? (bool)$_GET['open'] : null;

            $restaurants = $this->restaurantService->searchRestaurants($cuisineType, $isOpen);
            return Response::success('Restaurantes', $restaurants);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
