<?php
/**
 * Classe para Padronizar Respostas HTTP
 */

class Response {
    public static function success($message = '', $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public static function error($message = '', $errors = null, $statusCode = 400) {
        http_response_code($statusCode);
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public static function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
