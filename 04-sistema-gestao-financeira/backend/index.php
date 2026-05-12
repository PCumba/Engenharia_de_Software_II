<?php
/**
 * API Router - Sistema de Gestão Financeira
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
$basePath = '/04-sistema-gestao-financeira/backend';
$route = str_replace($basePath, '', $requestUri);
$route = trim($route, '/');

$method = $_SERVER['REQUEST_METHOD'];

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

// TRANSAÇÕES
if ($method === 'GET' && $route === 'api/summary') {
    $controller = new TransactionController($database);
    $response = $controller->getSummary();
    Response::json($response);
}

if ($method === 'POST' && $route === 'api/transactions') {
    $controller = new TransactionController($database);
    $response = $controller->create();
    Response::json($response);
}

if ($method === 'GET' && $route === 'api/transactions/period') {
    $controller = new TransactionController($database);
    $response = $controller->getByPeriod();
    Response::json($response);
}

if ($method === 'GET' && $route === 'api/transactions/recent') {
    $controller = new TransactionController($database);
    $response = $controller->getRecent();
    Response::json($response);
}

if ($method === 'PUT' && preg_match('/^api\/transactions\/(\d+)$/', $route, $matches)) {
    $controller = new TransactionController($database);
    $response = $controller->update($matches[1]);
    Response::json($response);
}

if ($method === 'DELETE' && preg_match('/^api\/transactions\/(\d+)$/', $route, $matches)) {
    $controller = new TransactionController($database);
    $response = $controller->delete($matches[1]);
    Response::json($response);
}

// ORÇAMENTOS
if ($method === 'GET' && $route === 'api/budgets') {
    $controller = new BudgetController($database);
    $response = $controller->getForMonth();
    Response::json($response);
}

if ($method === 'POST' && $route === 'api/budgets') {
    $controller = new BudgetController($database);
    $response = $controller->create();
    Response::json($response);
}

if ($method === 'PUT' && preg_match('/^api\/budgets\/(\d+)$/', $route, $matches)) {
    $controller = new BudgetController($database);
    $response = $controller->update($matches[1]);
    Response::json($response);
}

if ($method === 'DELETE' && preg_match('/^api\/budgets\/(\d+)$/', $route, $matches)) {
    $controller = new BudgetController($database);
    $response = $controller->delete($matches[1]);
    Response::json($response);
}

if ($method === 'GET' && $route === 'api/budgets/status') {
    $controller = new BudgetController($database);
    $response = $controller->checkStatus();
    Response::json($response);
}

// RELATÓRIOS
if ($method === 'GET' && $route === 'api/reports/expenses-category') {
    $controller = new ReportController($database);
    $response = $controller->getExpensesByCategory();
    Response::json($response);
}

if ($method === 'GET' && $route === 'api/reports/income-category') {
    $controller = new ReportController($database);
    $response = $controller->getIncomeByCategory();
    Response::json($response);
}

if ($method === 'GET' && $route === 'api/reports/monthly-evolution') {
    $controller = new ReportController($database);
    $response = $controller->getMonthlyEvolution();
    Response::json($response);
}

if ($method === 'GET' && $route === 'api/reports/period') {
    $controller = new ReportController($database);
    $response = $controller->getPeriodReport();
    Response::json($response);
}

// 404
http_response_code(404);
Response::json(Response::error('Endpoint não encontrado', null, 404));
?>
