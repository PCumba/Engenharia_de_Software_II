<?php

namespace App\Middleware;

use App\Services\JWTService;
use App\Utils\Response;

/**
 * Middleware de Autenticação
 */
class AuthMiddleware
{
    public static function handle(): void
    {
        $jwtService = new JWTService();
        
        // Extrair token do header
        $token = $jwtService->extractTokenFromHeader();
        
        if (!$token) {
            Response::unauthorized('Token de acesso não fornecido');
            return;
        }
        
        // Validar token
        $payload = $jwtService->validateToken($token);
        
        if (!$payload) {
            Response::unauthorized('Token de acesso inválido ou expirado');
            return;
        }
        
        // Armazenar informações do usuário na sessão
        $_SESSION['user_id'] = $payload['user_id'];
        $_SESSION['token_payload'] = $payload;
        
        // Verificar se o token precisa ser renovado
        $newToken = $jwtService->refreshIfNeeded($token);
        if ($newToken) {
            header('X-New-Token: ' . $newToken);
        }
    }
}