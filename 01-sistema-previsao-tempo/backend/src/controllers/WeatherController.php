<?php
/**
 * Controlador de Previsão do Tempo
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Weather.php';
require_once __DIR__ . '/../services/WeatherService.php';
require_once __DIR__ . '/../services/ExportService.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class WeatherController {
    private $db;
    private $weatherModel;
    private $weatherService;

    public function __construct() {
        $this->db = new Database();
        $this->weatherModel = new Weather($this->db);
        $this->weatherService = new WeatherService();
    }

    /**
     * Buscar previsão atual de uma cidade
     */
    public function getCurrentWeather() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'city' => 'required|min:2'
            ];

            $errors = Validator::validate($input, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            // Buscar dados da API
            $weather = $this->weatherService->getCurrentWeather($input['city'], $input['language'] ?? 'pt');

            // Salvar no histórico
            $this->weatherModel->saveSearch($payload['userId'], $weather['city'], $weather['country'], $weather);

            return Response::json(Response::success('Previsão obtida com sucesso', $weather));
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Buscar previsão de 5 dias
     */
    public function getFiveDayForecast() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'city' => 'required|min:2'
            ];

            $errors = Validator::validate($input, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            // Buscar dados da API
            $forecast = $this->weatherService->getFiveDayForecast($input['city'], $input['language'] ?? 'pt');

            return Response::json(Response::success('Previsão obtida com sucesso', $forecast));
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Obter histórico de buscas
     */
    public function getSearchHistory() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $history = $this->weatherModel->getSearchHistory($payload['userId']);

            return Response::json(Response::success('Histórico obtido com sucesso', $history));
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Adicionar localização aos favoritos
     */
    public function addFavorite() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'city' => 'required|min:2',
                'country' => 'required|min:2'
            ];

            $errors = Validator::validate($input, $rules);
            
            if (!Validator::isValid($errors)) {
                return Response::json(Response::error('Validação falhou', $errors, 422));
            }

            if ($this->weatherModel->addFavorite($payload['userId'], $input['city'], $input['country'])) {
                return Response::json(Response::success('Favorito adicionado com sucesso', null, 201));
            } else {
                return Response::json(Response::error('Erro ao adicionar favorito', null, 500));
            }
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Obter localizações favoritas
     */
    public function getFavorites() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $favorites = $this->weatherModel->getFavorites($payload['userId']);

            return Response::json(Response::success('Favoritos obtidos com sucesso', $favorites));
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Remover favorito
     */
    public function removeFavorite() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if ($this->weatherModel->removeFavorite($payload['userId'], $input['id'])) {
                return Response::json(Response::success('Favorito removido com sucesso'));
            } else {
                return Response::json(Response::error('Erro ao remover favorito', null, 500));
            }
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }

    /**
     * Exportar histórico em CSV
     */
    public function exportHistoryCSV() {
        try {
            $payload = Auth::checkToken();

            if (!$payload) {
                return Response::json(Response::error('Token inválido ou expirado', null, 401));
            }

            $history = $this->weatherModel->getSearchHistory($payload['userId'], 999);
            ExportService::exportToCSV($history);
        } catch (Exception $e) {
            return Response::json(Response::error('Erro: ' . $e->getMessage(), null, 500));
        }
    }
}
?>
