<?php
/**
 * API Router - Sistema de Pedido de Comida
 */

// Carregar variáveis de ambiente antes de qualquer getenv()
$envFile = __DIR__ . '/src/config/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    if ($env !== false) {
        foreach ($env as $key => $value) {
            putenv("$key=$value");
        }
    }
}

header('Access-Control-Allow-Origin: ' . (getenv('CORS_ORIGIN') ?: '*'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoloader
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

require_once __DIR__ . '/src/config/database.php';

$database = new Database();
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/05-sistema-pedido-comida/backend';
$route = str_replace($basePath, '', $requestUri);
$route = trim($route, '/');

$method = $_SERVER['REQUEST_METHOD'];

// AUTENTICAÇÃO
if ($method === 'POST' && $route === 'api/auth/register') {
    $controller = new AuthController($database);
    $response = $controller->register();
    Response::json($response);
    exit;
}

if ($method === 'POST' && $route === 'api/auth/login') {
    $controller = new AuthController($database);
    $response = $controller->login();
    Response::json($response);
    exit;
}

if ($method === 'GET' && $route === 'api/auth/me') {
    $controller = new AuthController($database);
    $response = $controller->me();
    Response::json($response);
    exit;
}

// RESTAURANTES
if ($method === 'GET' && $route === 'api/restaurants') {
    $controller = new RestaurantController($database);
    $response = $controller->getAll();
    Response::json($response);
    exit;
}

if ($method === 'GET' && preg_match('/^api\/restaurants\/(\d+)$/', $route, $matches)) {
    $controller = new RestaurantController($database);
    $response = $controller->getById($matches[1]);
    Response::json($response);
    exit;
}

if ($method === 'GET' && $route === 'api/restaurants/search') {
    $controller = new RestaurantController($database);
    $response = $controller->search();
    Response::json($response);
    exit;
}

// MENU
if ($method === 'GET' && preg_match('/^api\/restaurants\/(\d+)\/menu$/', $route, $matches)) {
    $controller = new MenuController($database);
    $response = $controller->getByRestaurant($matches[1]);
    Response::json($response);
    exit;
}

if ($method === 'GET' && preg_match('/^api\/restaurants\/(\d+)\/menu\/search$/', $route, $matches)) {
    $controller = new MenuController($database);
    $response = $controller->search($matches[1]);
    Response::json($response);
    exit;
}

// PEDIDOS
if ($method === 'POST' && $route === 'api/orders') {
    $controller = new OrderController($database);
    $response = $controller->create();
    Response::json($response);
    exit;
}

if ($method === 'GET' && $route === 'api/orders') {
    $controller = new OrderController($database);
    $response = $controller->getHistory();
    Response::json($response);
    exit;
}

if ($method === 'GET' && preg_match('/^api\/orders\/(\d+)$/', $route, $matches)) {
    $controller = new OrderController($database);
    $response = $controller->getById($matches[1]);
    Response::json($response);
    exit;
}

if ($method === 'GET' && preg_match('/^api\/orders\/(\d+)\/track$/', $route, $matches)) {
    $controller = new OrderController($database);
    $response = $controller->track($matches[1]);
    Response::json($response);
    exit;
}

// AVALIAÇÕES
if ($method === 'POST' && $route === 'api/reviews') {
    $controller = new ReviewController($database);
    $response = $controller->create();
    Response::json($response);
    exit;
}

if ($method === 'GET' && preg_match('/^api\/restaurants\/(\d+)\/reviews$/', $route, $matches)) {
    $controller = new ReviewController($database);
    $response = $controller->getByRestaurant($matches[1]);
    Response::json($response);
    exit;
}

if ($method === 'GET' && preg_match('/^api\/restaurants\/(\d+)\/reviews\/stats$/', $route, $matches)) {
    $controller = new ReviewController($database);
    $response = $controller->getStats($matches[1]);
    Response::json($response);
    exit;
}

// 404
http_response_code(404);
Response::json(Response::error('Endpoint não encontrado', null, 404));
?>
