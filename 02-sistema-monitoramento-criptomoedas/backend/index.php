<?php
/**
 * API Router - Sistema de Monitoramento de Criptomoedas
 * Endpoint: http://localhost/02-sistema-monitoramento-criptomoedas/backend
 */

// Carregar variáveis de ambiente antes de qualquer getenv()
$envFile = __DIR__ . '/src/config/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);

    if ($env !== false) {
        foreach ($env as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// CORS Headers
header('Access-Control-Allow-Origin: ' . (getenv('CORS_ORIGIN') ?: '*'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoloader de classes
spl_autoload_register(function ($class) {
    $basePath = __DIR__ . '/src';
    
    $paths = [
        $basePath . '/config/' . $class . '.php',
        $basePath . '/middleware/' . $class . '.php',
        $basePath . '/utils/' . $class . '.php',
        $basePath . '/models/' . $class . '.php',
        $basePath . '/services/' . $class . '.php',
        $basePath . '/controllers/' . $class . '.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Carregar configuração
require_once __DIR__ . '/src/config/database.php';

$database = new Database();

// Parse do request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/02-sistema-monitoramento-criptomoedas/backend';
$route = str_replace($basePath, '', $requestUri);
$route = trim($route, '/');

$method = $_SERVER['REQUEST_METHOD'];
$parts = explode('/', $route);

// Rotas de Autenticação
if ($method === 'POST' && $route === 'api/auth/register') {
    $controller = new AuthController($database);
    $response = $controller->register();
    Response::json($response); exit;
}

if ($method === 'POST' && $route === 'api/auth/login') {
    $controller = new AuthController($database);
    $response = $controller->login();
    Response::json($response); exit;
}

if ($method === 'GET' && $route === 'api/auth/me') {
    $controller = new AuthController($database);
    $response = $controller->me();
    Response::json($response); exit;
}

if ($method === 'POST' && $route === 'api/auth/logout') {
    $controller = new AuthController($database);
    $response = $controller->logout();
    Response::json($response); exit;
}

// Rotas de Criptomoedas
if ($method === 'POST' && $route === 'api/crypto/top') {
    $controller = new CryptoController($database);
    $response = $controller->getTop();
    Response::json($response); exit;
}

if ($method === 'POST' && $route === 'api/crypto/search') {
    $controller = new CryptoController($database);
    $response = $controller->search();
    Response::json($response); exit;
}

if ($method === 'GET' && preg_match('/^api\/crypto\/([a-z-]+)$/', $route, $matches)) {
    $controller = new CryptoController($database);
    $response = $controller->getDetails($matches[1]);
    Response::json($response); exit;
}

if ($method === 'GET' && preg_match('/^api\/crypto\/([a-z-]+)\/history/', $route, $matches)) {
    $controller = new CryptoController($database);
    $response = $controller->getPriceHistory($matches[1]);
    Response::json($response); exit;
}

// Rotas de Portfólio
if ($method === 'GET' && $route === 'api/portfolio') {
    $controller = new CryptoController($database);
    $response = $controller->getPortfolio();
    Response::json($response); exit;
}

if ($method === 'POST' && $route === 'api/portfolio') {
    $controller = new CryptoController($database);
    $response = $controller->addToPortfolio();
    Response::json($response); exit;
}

if ($method === 'DELETE' && preg_match('/^api\/portfolio\/(\d+)$/', $route, $matches)) {
    $controller = new CryptoController($database);
    $response = $controller->removeFromPortfolio($matches[1]);
    Response::json($response); exit;
}

// Rotas de Alertas
if ($method === 'GET' && $route === 'api/alerts') {
    $controller = new CryptoController($database);
    $response = $controller->getAlerts();
    Response::json($response); exit;
}

if ($method === 'POST' && $route === 'api/alerts') {
    $controller = new CryptoController($database);
    $response = $controller->createAlert();
    Response::json($response); exit;
}

if ($method === 'DELETE' && preg_match('/^api\/alerts\/(\d+)$/', $route, $matches)) {
    $controller = new CryptoController($database);
    $response = $controller->disableAlert($matches[1]);
    Response::json($response); exit;
}

// Rota não encontrada
http_response_code(404);
Response::json(Response::error('Endpoint não encontrado', null, 404));
?>
