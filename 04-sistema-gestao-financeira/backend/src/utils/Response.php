<?php
/**
 * Factory de Respostas HTTP
 */

class Response {
    public static function success($message = 'Sucesso', $data = null, $code = 200) {
        http_response_code($code);
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function error($message = 'Erro', $errors = null, $code = 400) {
        http_response_code($code);
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
    }

    public static function json($response) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public static function paginated($data, $total, $page = 1, $perPage = 10) {
        return [
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
