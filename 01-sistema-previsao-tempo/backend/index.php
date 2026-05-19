<?php
/**
 * API REST - Weather System
 * Arquivo Principal de Rotas
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . (getenv('CORS_ORIGIN') ?: 'http://localhost:4200'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Lidar com requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Carregar variáveis de ambiente
if (file_exists(__DIR__ . '/src/config/.env')) {
    $env = parse_ini_file(__DIR__ . '/src/config/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Incluir dependências
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/WeatherController.php';

// Parser da URL
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/01-sistema-previsao-tempo/backend';
$route = str_replace($basePath, '', $requestUri);
$route = trim($route, '/');

// Router simples
try {
    switch ($route) {
        // Autenticação
        case 'api/auth/register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new AuthController();
                $controller->register();
            }
            break;

        case 'api/auth/login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new AuthController();
                $controller->login();
            }
            break;

        case 'api/auth/me':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller = new AuthController();
                $controller->getCurrentUser();
            }
            break;

        case 'api/auth/preferences':
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $controller = new AuthController();
                $controller->updatePreferences();
            }
            break;

        case 'api/auth/logout':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new AuthController();
                $controller->logout();
            }
            break;

        case 'api/auth/forgot-password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new AuthController();
                $controller->requestPasswordReset();
            }
            break;

        case 'api/auth/validate-token':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new AuthController();
                $controller->validateResetToken();
            }
            break;

        case 'api/auth/reset-password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new AuthController();
                $controller->resetPassword();
            }
            break;

        case 'api/auth/change-password':
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $controller = new AuthController();
                $controller->changePassword();
            }
            break;

        case 'api/auth/profile':
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $controller = new AuthController();
                $controller->updateProfile();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $controller = new AuthController();
                $controller->deleteAccount();
            }
            break;

        case 'api/auth/activity-log':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller = new AuthController();
                $controller->getActivityLog();
            }
            break;

        // Previsão do Tempo
        case 'api/weather/current':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new WeatherController();
                $controller->getCurrentWeather();
            }
            break;

        case 'api/weather/forecast':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new WeatherController();
                $controller->getFiveDayForecast();
            }
            break;

        case 'api/weather/history':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller = new WeatherController();
                $controller->getSearchHistory();
            }
            break;

        case 'api/weather/favorites':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller = new WeatherController();
                $controller->getFavorites();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new WeatherController();
                $controller->addFavorite();
            }
            break;

        case 'api/weather/favorites/remove':
            if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $controller = new WeatherController();
                $controller->removeFavorite();
            }
            break;

        case 'api/weather/export/csv':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller = new WeatherController();
                $controller->exportHistoryCSV();
            }
            break;

        // Health check
        case 'api/health':
            echo json_encode([
                'status' => 'ok',
                'timestamp' => date('Y-m-d H:i:s'),
                'service' => 'Weather System API'
            ]);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Rota não encontrada',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro do servidor: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
