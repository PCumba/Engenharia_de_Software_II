<?php
/**
 * Controller - Avaliação
 */

class ReviewController {
    private $reviewService;

    public function __construct($database) {
        $this->reviewService = new ReviewService($database);
    }

    public function create() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $data = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'orderId' => 'required|numeric',
                'restaurantId' => 'required|numeric',
                'rating' => 'required|numeric',
                'comment' => 'required|min:5'
            ];

            $errors = Validator::validate($data, $rules);
            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            if ($data['rating'] < 1 || $data['rating'] > 5) {
                return Response::error('Rating deve estar entre 1 e 5', null, 422);
            }

            $reviewId = $this->reviewService->createReview(
                $data['orderId'],
                $data['restaurantId'],
                $token['userId'],
                $data['rating'],
                $data['comment']
            );

            return Response::success('Avaliação registada com sucesso', ['id' => $reviewId], 201);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getByRestaurant($restaurantId) {
        try {
            $reviews = $this->reviewService->getRestaurantReviews($restaurantId);
            return Response::success('Avaliações do restaurante', $reviews);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getStats($restaurantId) {
        try {
            $stats = $this->reviewService->getReviewStats($restaurantId);
            return Response::success('Estatísticas de avaliações', $stats);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
