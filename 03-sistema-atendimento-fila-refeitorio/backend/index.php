<?php
/**
 * API Router - Sistema de Fila Refeitório
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
$basePath = '/03-sistema-atendimento-fila-refeitorio/backend';
$route = str_replace($basePath, '', $requestUri);
$route = trim($route, '/');

$method = $_SERVER['REQUEST_METHOD'];
$parts = explode('/', $route);

// AUTENTICAÇÃO
if ($method === 'POST' && $route === 'api/auth/register') {
    $controller = new AuthController($database);
    $response = $controller->register();
    Response::json($response);
}

if ($method === 'POST' && $route === 'api/auth/login') {
    $controller = new AuthController($database);
    $response = $controller->login();
    Response::json($response);
}

if ($method === 'GET' && $route === 'api/auth/me') {
    $controller = new AuthController($database);
    $response = $controller->me();
    Response::json($response);
}

// FILA PÚBLICA
if ($method === 'GET' && $route === 'api/services') {
    $controller = new QueueController($database);
    $response = $controller->getServices();
    Response::json($response);
}

if ($method === 'GET' && preg_match('/^api\/queue\/(\d+)$/', $route, $matches)) {
    $controller = new QueueController($database);
    $response = $controller->getQueueInfo($matches[1]);
    Response::json($response);
}

if ($method === 'POST' && $route === 'api/tickets') {
    $controller = new QueueController($database);
    $response = $controller->createTicket();
    Response::json($response);
}

if ($method === 'GET' && $route === 'api/tickets/my') {
    $controller = new QueueController($database);
    $response = $controller->getMyTicket();
    Response::json($response);
}

if ($method === 'DELETE' && preg_match('/^api\/tickets\/(\d+)$/', $route, $matches)) {
    $controller = new QueueController($database);
    $response = $controller->cancelTicket($matches[1]);
    Response::json($response);
}

// ADMINISTRAÇÃO
if ($method === 'GET' && preg_match('/^api\/admin\/queue\/(\d+)$/', $route, $matches)) {
    $controller = new AdminController($database);
    $response = $controller->getQueue($matches[1]);
    Response::json($response);
}

if ($method === 'POST' && preg_match('/^api\/admin\/call\/(\d+)$/', $route, $matches)) {
    $controller = new AdminController($database);
    $response = $controller->callNextTicket($matches[1]);
    Response::json($response);
}

if ($method === 'POST' && preg_match('/^api\/admin\/complete\/(\d+)$/', $route, $matches)) {
    $controller = new AdminController($database);
    $response = $controller->completeTicket($matches[1]);
    Response::json($response);
}

if ($method === 'GET' && preg_match('/^api\/admin\/stats\/(\d+)$/', $route, $matches)) {
    $controller = new AdminController($database);
    $response = $controller->getStats($matches[1]);
    Response::json($response);
}

// 404
http_response_code(404);
Response::json(Response::error('Endpoint não encontrado', null, 404));
?>
