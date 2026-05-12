<?php
/**
 * Controller de Administração
 */

class AdminController {
    private $queueModel;

    public function __construct($database) {
        $this->queueModel = new Queue($database);
    }

    /**
     * Verificar se é admin
     */
    private function isAdmin($token) {
        return $token && $token['role'] === 'admin';
    }

    /**
     * Obter fila de um serviço
     */
    public function getQueue($serviceId) {
        try {
            $token = Auth::checkToken();

            if (!$this->isAdmin($token)) {
                return Response::error('Permissão negada', null, 403);
            }

            $queue = $this->queueModel->getQueueByService($serviceId);
            return Response::success('Fila do serviço', $queue);
        } catch (Exception $e) {
            return Response::error('Erro ao obter fila', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Chamar próximo ticket
     */
    public function callNextTicket($serviceId) {
        try {
            $token = Auth::checkToken();

            if (!$this->isAdmin($token)) {
                return Response::error('Permissão negada', null, 403);
            }

            $ticket = $this->queueModel->callNextTicket($serviceId);

            if (!$ticket) {
                return Response::error('Nenhum ticket disponível', null, 404);
            }

            return Response::success('Próximo ticket chamado', $ticket);
        } catch (Exception $e) {
            return Response::error('Erro ao chamar ticket', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Completar atendimento
     */
    public function completeTicket($ticketId) {
        try {
            $token = Auth::checkToken();

            if (!$this->isAdmin($token)) {
                return Response::error('Permissão negada', null, 403);
            }

            $this->queueModel->completeTicket($ticketId);

            return Response::success('Atendimento concluído');
        } catch (Exception $e) {
            return Response::error('Erro ao completar', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter estatísticas
     */
    public function getStats($serviceId) {
        try {
            $token = Auth::checkToken();

            if (!$this->isAdmin($token)) {
                return Response::error('Permissão negada', null, 403);
            }

            $stats = $this->queueModel->getQueueStats($serviceId);

            return Response::success('Estatísticas da fila', $stats);
        } catch (Exception $e) {
            return Response::error('Erro ao obter stats', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
