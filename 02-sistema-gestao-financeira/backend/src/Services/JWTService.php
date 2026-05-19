<?php

namespace App\Services;

/**
 * Serviço para gerenciamento de JWT
 * Implementação simples sem biblioteca externa
 */
class JWTService
{
    private string $secret;
    private string $algorithm;
    private int $expiration;
    private int $refreshExpiration;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/app.php';
        
        $this->secret = $config['jwt']['secret'];
        $this->algorithm = $config['jwt']['algorithm'];
        $this->expiration = $config['jwt']['expiration'];
        $this->refreshExpiration = $config['jwt']['refresh_expiration'];
    }

    /**
     * Gerar token de acesso
     */
    public function generateToken(int $userId): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];

        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + $this->expiration,
            'type' => 'access'
        ];

        return $this->encode($header, $payload);
    }

    /**
     * Gerar refresh token
     */
    public function generateRefreshToken(int $userId): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];

        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + $this->refreshExpiration,
            'type' => 'refresh'
        ];

        return $this->encode($header, $payload);
    }

    /**
     * Validar token de acesso
     */
    public function validateToken(string $token): ?array
    {
        try {
            $payload = $this->decode($token);
            
            if (!$payload || $payload['type'] !== 'access') {
                return null;
            }
            
            if ($payload['exp'] < time()) {
                return null;
            }
            
            return $payload;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validar refresh token
     */
    public function validateRefreshToken(string $token): ?array
    {
        try {
            $payload = $this->decode($token);
            
            if (!$payload || $payload['type'] !== 'refresh') {
                return null;
            }
            
            if ($payload['exp'] < time()) {
                return null;
            }
            
            return $payload;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Codificar JWT
     */
    private function encode(array $header, array $payload): string
    {
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = $this->sign($headerEncoded . '.' . $payloadEncoded);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    /**
     * Decodificar JWT
     */
    private function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        [$headerEncoded, $payloadEncoded, $signature] = $parts;
        
        // Verificar assinatura
        $expectedSignature = $this->sign($headerEncoded . '.' . $payloadEncoded);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }
        
        // Decodificar payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        
        return $payload;
    }

    /**
     * Assinar dados
     */
    private function sign(string $data): string
    {
        $signature = hash_hmac('sha256', $data, $this->secret, true);
        return $this->base64UrlEncode($signature);
    }

    /**
     * Codificar base64 URL-safe
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificar base64 URL-safe
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Extrair token do header Authorization
     */
    public function extractTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if (!$authHeader) {
            return null;
        }
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Verificar se token está expirado
     */
    public function isExpired(array $payload): bool
    {
        return $payload['exp'] < time();
    }

    /**
     * Obter tempo restante do token em segundos
     */
    public function getTimeToExpiry(array $payload): int
    {
        return max(0, $payload['exp'] - time());
    }

    /**
     * Renovar token se estiver próximo do vencimento
     */
    public function refreshIfNeeded(string $token, int $threshold = 300): ?string
    {
        $payload = $this->validateToken($token);
        
        if (!$payload) {
            return null;
        }
        
        $timeToExpiry = $this->getTimeToExpiry($payload);
        
        // Se faltam menos que o threshold (5 minutos por padrão), renovar
        if ($timeToExpiry < $threshold) {
            return $this->generateToken($payload['user_id']);
        }
        
        return null;
    }
}