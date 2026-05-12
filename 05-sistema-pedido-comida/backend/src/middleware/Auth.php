<?php
/**
 * Middleware de Autenticação com JWT e Password Hashing
 */

class Auth {
    private static $secret = null;

    private static function getSecret() {
        if (self::$secret === null) {
            self::$secret = getenv('JWT_SECRET');
            if (strlen(self::$secret) < 32) {
                throw new Exception('JWT_SECRET deve ter no mínimo 32 caracteres');
            }
        }
        return self::$secret;
    }

    public static function generateToken($userId, $email, $role = 'customer') {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'userId' => $userId,
            'email' => $email,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + (getenv('JWT_EXPIRY') ?: 3600)
        ]);

        $headerEncoded = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::getSecret(), true);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::getSecret(), true);
        $signatureExpected = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        if ($signatureEncoded !== $signatureExpected) return null;

        $payload = json_decode(base64_decode(strtr($payloadEncoded, '-_', '+/')), true);
        if ($payload['exp'] < time()) return null;

        return $payload;
    }

    public static function checkToken() {
        $headers = getallheaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) return null;
        return self::verifyToken($token);
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
?>
