<?php
/**
 * Middleware de Autenticação
 */

class Auth {
    private static $jwtSecret;

    public static function init() {
        self::$jwtSecret = getenv('JWT_SECRET') ?: 'default_secret';
    }

    public static function generateToken($userId, $email, $role = 'customer') {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $payload = [
            'iss' => 'fila-refeitorio',
            'aud' => 'queue-app',
            'iat' => time(),
            'exp' => time() + (int)getenv('JWT_EXPIRY'),
            'userId' => $userId,
            'email' => $email,
            'role' => $role
        ];

        $headerEncoded = base64_encode(json_encode($header));
        $payloadEncoded = base64_encode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::$jwtSecret, true);
        $signatureEncoded = base64_encode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function verifyToken($token) {
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return false;
            }

            $headerDecoded = json_decode(base64_decode($parts[0]), true);
            $payloadDecoded = json_decode(base64_decode($parts[1]), true);
            $signatureDecoded = base64_decode($parts[2]);

            $signature = hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", self::$jwtSecret, true);

            if ($signatureDecoded !== $signature) {
                return false;
            }

            if ($payloadDecoded['exp'] < time()) {
                return false;
            }

            return $payloadDecoded;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function checkToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (empty($authHeader)) {
            return false;
        }

        $token = str_replace('Bearer ', '', $authHeader);
        return self::verifyToken($token);
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

Auth::init();
?>
