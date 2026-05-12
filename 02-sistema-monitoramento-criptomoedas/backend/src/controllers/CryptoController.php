<?php
/**
 * Controller de Criptomoedas
 */

class CryptoController {
    private $cryptoModel;
    private $cryptoService;

    public function __construct($database) {
        $this->cryptoModel = new Cryptocurrency($database);
        $this->cryptoService = new CryptoService();
    }

    /**
     * Obter top criptomoedas
     */
    public function getTop() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $limit = $data['limit'] ?? 25;

            $cryptos = $this->cryptoService->getTopCryptos($limit);

            return Response::success('Top criptomoedas', $cryptos);
        } catch (Exception $e) {
            return Response::error('Erro ao buscar criptomoedas', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Buscar criptomoeda
     */
    public function search() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $rules = ['query' => 'required|min:1'];
            $errors = Validator::validate($data, $rules);

            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            $results = $this->cryptoService->searchCrypto($data['query']);

            return Response::success('Resultados da busca', $results);
        } catch (Exception $e) {
            return Response::error('Erro na busca', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter detalhes de uma criptomoeda
     */
    public function getDetails($cryptoId) {
        try {
            $details = $this->cryptoService->getCryptoDetails($cryptoId);

            return Response::success('Detalhes da criptomoeda', $details);
        } catch (Exception $e) {
            return Response::error('Criptomoeda não encontrada', ['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Obter histórico de preços
     */
    public function getPriceHistory($cryptoId) {
        try {
            $days = $_GET['days'] ?? 7;

            $history = $this->cryptoService->getPriceHistory($cryptoId, $days);

            return Response::success('Histórico de preços', $history);
        } catch (Exception $e) {
            return Response::error('Erro ao buscar histórico', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter portfólio do utilizador
     */
    public function getPortfolio() {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $portfolio = $this->cryptoModel->getPortfolio($token['userId']);

            return Response::success('Portfólio do utilizador', $portfolio);
        } catch (Exception $e) {
            return Response::error('Erro ao buscar portfólio', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Adicionar ao portfólio
     */
    public function addToPortfolio() {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'cryptoId' => 'required',
                'quantity' => 'required|numeric',
                'purchasePrice' => 'required|numeric'
            ];

            $errors = Validator::validate($data, $rules);

            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            // Buscar símbolo e nome da cripto
            $cryptoDetails = $this->cryptoService->searchCrypto($data['cryptoId']);
            
            if (empty($cryptoDetails)) {
                return Response::error('Criptomoeda não encontrada', null, 404);
            }

            $crypto = $cryptoDetails[0];

            $this->cryptoModel->addToPortfolio(
                $token['userId'],
                $crypto['id'],
                $crypto['symbol'],
                $data['quantity'],
                $data['purchasePrice']
            );

            return Response::success('Criptomoeda adicionada ao portfólio', null, 201);
        } catch (Exception $e) {
            return Response::error('Erro ao adicionar ao portfólio', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remover do portfólio
     */
    public function removeFromPortfolio($portfolioId) {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $this->cryptoModel->removeFromPortfolio($token['userId'], $portfolioId);

            return Response::success('Removido do portfólio com sucesso');
        } catch (Exception $e) {
            return Response::error('Erro ao remover', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter alertas de preço
     */
    public function getAlerts() {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $alerts = $this->cryptoModel->getAlerts($token['userId']);

            return Response::success('Alertas do utilizador', $alerts);
        } catch (Exception $e) {
            return Response::error('Erro ao buscar alertas', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Criar alerta de preço
     */
    public function createAlert() {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'cryptoId' => 'required',
                'priceTarget' => 'required|numeric',
                'alertType' => 'required'
            ];

            $errors = Validator::validate($data, $rules);

            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            if (!in_array($data['alertType'], ['above', 'below'])) {
                return Response::error('Tipo de alerta inválido', null, 422);
            }

            // Buscar símbolo da cripto
            $cryptoDetails = $this->cryptoService->searchCrypto($data['cryptoId']);
            
            if (empty($cryptoDetails)) {
                return Response::error('Criptomoeda não encontrada', null, 404);
            }

            $crypto = $cryptoDetails[0];

            $this->cryptoModel->createAlert(
                $token['userId'],
                $crypto['id'],
                $crypto['symbol'],
                $data['priceTarget'],
                $data['alertType']
            );

            return Response::success('Alerta criado com sucesso', null, 201);
        } catch (Exception $e) {
            return Response::error('Erro ao criar alerta', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Desativar alerta
     */
    public function disableAlert($alertId) {
        try {
            $token = Auth::checkToken();

            if (!$token) {
                return Response::error('Não autenticado', null, 401);
            }

            $this->cryptoModel->disableAlert($token['userId'], $alertId);

            return Response::success('Alerta desativado com sucesso');
        } catch (Exception $e) {
            return Response::error('Erro ao desativar alerta', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
