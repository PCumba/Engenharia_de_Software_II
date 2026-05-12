<?php
/**
 * Controller - Menu
 */

class MenuController {
    private $menuService;

    public function __construct($database) {
        $this->menuService = new MenuService($database);
    }

    public function getByRestaurant($restaurantId) {
        try {
            $menu = $this->menuService->getMenuByRestaurant($restaurantId);
            return Response::success('Menu do restaurante', $menu);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getByCategory($restaurantId, $category) {
        try {
            $items = $this->menuService->getItemsByCategory($restaurantId, $category);
            return Response::success('Itens da categoria', $items);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function search($restaurantId) {
        try {
            $query = $_GET['q'] ?? '';
            if (strlen($query) < 2) {
                return Response::error('Query muito curta', null, 400);
            }

            $items = $this->menuService->searchItems($restaurantId, $query);
            return Response::success('Resultados da busca', $items);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
