<?php
/**
 * Controller de Fila
 */

class QueueController {
    private $queueModel;
    private $serviceModel;
    private $queueService;

    public function __construct($database) {
        $this->queueModel = new Queue($database);
        $this->serviceModel = new Service($database);
        $this->queueService = new QueueService($database);
    }

    /**
     * Listar serviços disponíveis
     */
    public function getServices() {
        try {
            $services = $this->serviceModel->getAll();
            return Response::success('Serviços disponíveis', $services);
        } catch (Exception $e) {
            return Response::error('Erro ao listar serviços', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter informações da fila de um serviço
     */
    public function getQueueInfo($serviceId) {
        try {
            $info = $this->queueService->getQueueInfo($serviceId);
            return Response::success('Informações da fila', $info);
        } catch (Exception $e) {
            return Response::error('Erro ao obter fila', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Criar novo ticket
     */
    public function createTicket() {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['serviceId'])) {
                return Response::error('Serviço obrigatório', null, 422);
            }

            // Verificar se serviço existe
            $service = $this->serviceModel->findById($data['serviceId']);
            if (!$service) {
                return Response::error('Serviço não encontrado', null, 404);
            }

            // Criar ticket
            $ticket = $this->queueModel->createTicket($token['userId'], $data['serviceId']);

            return Response::success('Ticket criado com sucesso', [
                'ticketId' => $ticket['id'],
                'ticketNumber' => $ticket['ticketNumber'],
                'status' => $ticket['status']
            ], 201);
        } catch (Exception $e) {
            return Response::error('Erro ao criar ticket', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter ticket ativo do utilizador
     */
    public function getMyTicket() {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $ticket = $this->queueModel->getUserActiveTicket($token['userId']);

            if (!$ticket) {
                return Response::error('Nenhum ticket ativo', null, 404);
            }

            // Obter posição na fila
            $position = $this->queueModel->getUserPosition($token['userId'], $ticket['service_id']);

            return Response::success('Ticket do utilizador', [
                'ticket' => $ticket,
                'position' => $position
            ]);
        } catch (Exception $e) {
            return Response::error('Erro ao obter ticket', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancelar ticket
     */
    public function cancelTicket($ticketId) {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $this->queueModel->cancelTicket($ticketId);

            return Response::success('Ticket cancelado com sucesso');
        } catch (Exception $e) {
            return Response::error('Erro ao cancelar ticket', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
