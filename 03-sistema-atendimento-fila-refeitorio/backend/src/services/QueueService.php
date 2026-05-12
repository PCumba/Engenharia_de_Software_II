<?php
/**
 * Serviço de Gerenciamento de Fila
 */

class QueueService {
    private $queueModel;
    private $serviceModel;

    public function __construct($database) {
        $this->queueModel = new Queue($database);
        $this->serviceModel = new Service($database);
    }

    /**
     * Calcular tempo estimado de espera
     */
    public function estimateWaitTime($serviceId) {
        try {
            $stats = $this->queueModel->getQueueStats($serviceId);
            
            // Assumir 5 minutos por ticket
            $estimatePerTicket = 5;
            $estimatedWait = ($stats['waiting_count'] + ($stats['calling_count'] * 0.5)) * $estimatePerTicket;

            return ceil($estimatedWait);
        } catch (Exception $e) {
            throw new Exception("Erro ao calcular tempo: " . $e->getMessage());
        }
    }

    /**
     * Obter informações da fila em tempo real
     */
    public function getQueueInfo($serviceId) {
        try {
            $service = $this->serviceModel->findById($serviceId);
            $queue = $this->queueModel->getQueueByService($serviceId);
            $stats = $this->queueModel->getQueueStats($serviceId);
            
            $waitingTicket = count($queue) > 0 ? $queue[0] : null;

            return [
                'service' => $service,
                'queue' => $queue,
                'stats' => $stats,
                'currentCalling' => $waitingTicket,
                'estimatedWait' => $this->estimateWaitTime($serviceId)
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao obter info da fila: " . $e->getMessage());
        }
    }
}
?>
