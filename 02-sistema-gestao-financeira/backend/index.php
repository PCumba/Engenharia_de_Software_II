<?php
/**
 * Sistema de Gestão Financeira Pessoal - API Backend
 * Ponto de entrada principal da aplicação
 */

require_once __DIR__ . '/src/config/bootstrap.php';

use App\Core\Router;
use App\Core\Database;
use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

// Configurar CORS
CorsMiddleware::handle();

// Inicializar roteador
$router = new Router();

// Middleware global de autenticação (exceto rotas públicas)
$publicRoutes = ['/auth/login', '/auth/register', '/auth/forgot-password'];
$currentRoute = $_SERVER['REQUEST_URI'];

if (!in_array($currentRoute, $publicRoutes)) {
    AuthMiddleware::handle();
}

try {
    // Definir rotas da API
    
    // Rotas de Autenticação
    $router->post('/auth/login', 'AuthController@login');
    $router->post('/auth/register', 'AuthController@register');
    $router->post('/auth/logout', 'AuthController@logout');
    $router->post('/auth/refresh', 'AuthController@refresh');
    $router->post('/auth/forgot-password', 'AuthController@forgotPassword');
    $router->post('/auth/reset-password', 'AuthController@resetPassword');
    
    // Rotas de Usuário
    $router->get('/user/profile', 'UserController@getProfile');
    $router->put('/user/profile', 'UserController@updateProfile');
    $router->post('/user/change-password', 'UserController@changePassword');
    
    // Rotas de Contas Bancárias
    $router->get('/accounts', 'AccountController@index');
    $router->post('/accounts', 'AccountController@create');
    $router->get('/accounts/{id}', 'AccountController@show');
    $router->put('/accounts/{id}', 'AccountController@update');
    $router->delete('/accounts/{id}', 'AccountController@delete');
    $router->get('/accounts/{id}/balance', 'AccountController@getBalance');
    
    // Rotas de Categorias
    $router->get('/categories', 'CategoryController@index');
    $router->post('/categories', 'CategoryController@create');
    $router->get('/categories/{id}', 'CategoryController@show');
    $router->put('/categories/{id}', 'CategoryController@update');
    $router->delete('/categories/{id}', 'CategoryController@delete');
    
    // Rotas de Transações
    $router->get('/transactions', 'TransactionController@index');
    $router->post('/transactions', 'TransactionController@create');
    $router->get('/transactions/{id}', 'TransactionController@show');
    $router->put('/transactions/{id}', 'TransactionController@update');
    $router->delete('/transactions/{id}', 'TransactionController@delete');
    $router->post('/transactions/import', 'TransactionController@import');
    $router->post('/transactions/bulk', 'TransactionController@bulkCreate');
    
    // Rotas de Transferências
    $router->post('/transfers', 'TransferController@create');
    $router->get('/transfers', 'TransferController@index');
    $router->get('/transfers/{id}', 'TransferController@show');
    
    // Rotas de Orçamentos
    $router->get('/budgets', 'BudgetController@index');
    $router->post('/budgets', 'BudgetController@create');
    $router->get('/budgets/{id}', 'BudgetController@show');
    $router->put('/budgets/{id}', 'BudgetController@update');
    $router->delete('/budgets/{id}', 'BudgetController@delete');
    $router->get('/budgets/{id}/status', 'BudgetController@getStatus');
    
    // Rotas de Metas
    $router->get('/goals', 'GoalController@index');
    $router->post('/goals', 'GoalController@create');
    $router->get('/goals/{id}', 'GoalController@show');
    $router->put('/goals/{id}', 'GoalController@update');
    $router->delete('/goals/{id}', 'GoalController@delete');
    $router->post('/goals/{id}/progress', 'GoalController@updateProgress');
    
    // Rotas de Relatórios
    $router->get('/reports/dashboard', 'ReportController@dashboard');
    $router->get('/reports/income-expense', 'ReportController@incomeExpense');
    $router->get('/reports/category-analysis', 'ReportController@categoryAnalysis');
    $router->get('/reports/monthly-summary', 'ReportController@monthlySummary');
    $router->get('/reports/budget-performance', 'ReportController@budgetPerformance');
    $router->post('/reports/export', 'ReportController@export');
    
    // Rotas de Alertas
    $router->get('/alerts', 'AlertController@index');
    $router->post('/alerts', 'AlertController@create');
    $router->put('/alerts/{id}', 'AlertController@update');
    $router->delete('/alerts/{id}', 'AlertController@delete');
    $router->post('/alerts/{id}/mark-read', 'AlertController@markAsRead');
    
    // Rotas de Câmbio (API Externa)
    $router->get('/exchange-rates', 'ExchangeRateController@getRates');
    $router->get('/exchange-rates/convert', 'ExchangeRateController@convert');
    
    // Executar roteamento
    $router->dispatch();
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    Response::error('Erro interno do servidor', 500);
}