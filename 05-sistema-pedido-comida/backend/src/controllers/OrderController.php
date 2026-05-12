<?php
/**
 * Controller - Pedido
 */

class OrderController {
    private $orderService;

    public function __construct($database) {
        $this->orderService = new OrderService($database);
    }

    public function create() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $data = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'restaurantId' => 'required|numeric',
                'items' => 'required',
                'deliveryAddress' => 'required',
                'deliveryNotes' => ''
            ];

            $errors = Validator::validate($data, $rules);
            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            $orderId = $this->orderService->createOrder(
                $token['userId'],
                $data['restaurantId'],
                $data['items'],
                $data['deliveryAddress'],
                $data['deliveryNotes'] ?? ''
            );

            return Response::success('Pedido criado com sucesso', ['id' => $orderId], 201);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getHistory() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $orders = $this->orderService->getOrderHistory($token['userId']);
            return Response::success('Histórico de pedidos', $orders);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getById($orderId) {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $order = $this->orderService->getOrderDetails($orderId, $token['userId']);
            if (!$order) {
                return Response::error('Pedido não encontrado', null, 404);
            }

            return Response::success('Detalhes do pedido', $order);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function track($orderId) {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $order = $this->orderService->trackOrder($orderId, $token['userId']);
            if (!$order) {
                return Response::error('Pedido não encontrado', null, 404);
            }

            return Response::success('Rastreio do pedido', $order);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
