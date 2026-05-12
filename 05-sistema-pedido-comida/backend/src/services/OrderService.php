<?php
/**
 * Service - Pedido
 */

class OrderService {
    private $orderModel;
    private $orderItemModel;
    private $menuItemModel;
    private $database;

    public function __construct($database) {
        $this->orderModel = new Order($database);
        $this->orderItemModel = new OrderItem($database);
        $this->menuItemModel = new MenuItem($database);
        $this->database = $database;
    }

    public function createOrder($userId, $restaurantId, $items, $deliveryAddress, $deliveryNotes) {
        $this->database->beginTransaction();

        try {
            $totalPrice = 0;

            foreach ($items as $item) {
                $menuItem = $this->menuItemModel->findById($item['menuItemId']);
                if (!$menuItem) {
                    throw new Exception('Item não existe: ' . $item['menuItemId']);
                }
                $totalPrice += $menuItem['price'] * $item['quantity'];
            }

            $orderId = $this->orderModel->create($userId, $restaurantId, $totalPrice, $deliveryAddress, $deliveryNotes);

            foreach ($items as $item) {
                $menuItem = $this->menuItemModel->findById($item['menuItemId']);
                $this->orderItemModel->create($orderId, $item['menuItemId'], $item['quantity'], $menuItem['price']);
            }

            $this->database->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    public function getOrderHistory($userId) {
        $orders = $this->orderModel->getByUser($userId);

        foreach ($orders as &$order) {
            $order['items'] = $this->orderItemModel->getByOrder($order['id']);
        }

        return $orders;
    }

    public function getOrderDetails($orderId, $userId = null) {
        $order = $this->orderModel->findById($orderId, $userId);
        if (!$order) return null;

        $order['items'] = $this->orderItemModel->getByOrder($orderId);
        return $order;
    }

    public function trackOrder($orderId, $userId) {
        return $this->getOrderDetails($orderId, $userId);
    }
}
?>
