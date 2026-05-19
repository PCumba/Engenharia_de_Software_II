<?php

namespace App\Middleware;

/**
 * Middleware de CORS
 */
class CorsMiddleware
{
    public static function handle(): void
    {
        $config = require __DIR__ . '/../config/app.php';
        $corsConfig = $config['api']['cors'];
        
        // Obter origem da requisição
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Verificar se a origem é permitida
        if (in_array($origin, $corsConfig['allowed_origins']) || in_array('*', $corsConfig['allowed_origins'])) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        // Definir headers CORS
        header('Access-Control-Allow-Methods: ' . implode(', ', $corsConfig['allowed_methods']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $corsConfig['allowed_headers']));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 horas
        
        // Responder a requisições OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}