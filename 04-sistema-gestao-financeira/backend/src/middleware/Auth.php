<?php
/**
 * Middleware de Autenticação e JWT
 */

class Auth {
    public static function generateToken($userId, $email) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        
        $payload = [
            'userId' => $userId,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + (int)getenv('JWT_EXPIRY')
        ];
        
        $payload = base64_encode(json_encode($payload));
        $signature = base64_encode(hash_hmac(
            'sha256',
            "$header.$payload",
            getenv('JWT_SECRET'),
            true
        ));

        return "$header.$payload.$signature";
    }

    public static function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;
        
        $expectedSignature = base64_encode(hash_hmac(
            'sha256',
            "$header.$payload",
            getenv('JWT_SECRET'),
            true
        ));

        if ($signature !== $expectedSignature) {
            return false;
        }

        $decoded = json_decode(base64_decode($payload), true);
        
        if ($decoded['exp'] < time()) {
            return false;
        }

        return $decoded;
    }

    public static function checkToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            return null;
        }

        return self::verifyToken($matches[1]);
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
?>
