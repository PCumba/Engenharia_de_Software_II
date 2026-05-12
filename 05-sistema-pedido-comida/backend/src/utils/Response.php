<?php
/**
 * Factory de Respostas HTTP Padronizadas
 */

class Response {
    public static function success($message, $data = null, $code = 200) {
        http_response_code($code);
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function error($message, $errors = null, $code = 400) {
        http_response_code($code);
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
    }

    public static function json($response) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public static function paginated($data, $total, $page, $perPage) {
        http_response_code(200);
        return [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'pages' => ceil($total / $perPage)
            ]
        ];
    }
}
?>
