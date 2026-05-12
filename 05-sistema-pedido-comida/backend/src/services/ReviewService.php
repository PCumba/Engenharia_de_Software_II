<?php
/**
 * Service - Avaliação
 */

class ReviewService {
    private $reviewModel;
    private $orderModel;

    public function __construct($database) {
        $this->reviewModel = new Review($database);
        $this->orderModel = new Order($database);
    }

    public function createReview($orderId, $restaurantId, $userId, $rating, $comment) {
        if ($this->reviewModel->checkIfReviewed($orderId)) {
            throw new Exception('Este pedido já foi avaliado');
        }

        $order = $this->orderModel->findById($orderId, $userId);
        if (!$order) {
            throw new Exception('Pedido não encontrado');
        }

        $reviewId = $this->reviewModel->create($orderId, $restaurantId, $userId, $rating, $comment);

        // Atualizar rating do restaurante
        $this->orderModel->updateStatus($orderId, 'reviewed');

        return $reviewId;
    }

    public function getRestaurantReviews($restaurantId) {
        return $this->reviewModel->getByRestaurant($restaurantId);
    }

    public function getReviewStats($restaurantId) {
        return $this->reviewModel->getStats($restaurantId);
    }
}
?>
