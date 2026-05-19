<?php

namespace App\Utils;

/**
 * Classe para padronizar respostas da API
 */
class Response
{
    /**
     * Resposta de sucesso
     */
    public static function success($data = null, string $message = 'Sucesso', int $statusCode = 200): void
    {
        http_response_code($statusCode);
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Resposta de erro
     */
    public static function error(string $message = 'Erro', int $statusCode = 400, $errors = null): void
    {
        http_response_code($statusCode);
        
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('c')
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Resposta paginada
     */
    public static function paginated(array $data, array $pagination, string $message = 'Sucesso'): void
    {
        self::success([
            'items' => $data,
            'pagination' => $pagination
        ], $message);
    }

    /**
     * Resposta de validação
     */
    public static function validation(array $errors, string $message = 'Dados inválidos'): void
    {
        self::error($message, 422, $errors);
    }

    /**
     * Resposta não autorizada
     */
    public static function unauthorized(string $message = 'Não autorizado'): void
    {
        self::error($message, 401);
    }

    /**
     * Resposta proibida
     */
    public static function forbidden(string $message = 'Acesso negado'): void
    {
        self::error($message, 403);
    }

    /**
     * Resposta não encontrada
     */
    public static function notFound(string $message = 'Recurso não encontrado'): void
    {
        self::error($message, 404);
    }

    /**
     * Resposta de conflito
     */
    public static function conflict(string $message = 'Conflito de dados'): void
    {
        self::error($message, 409);
    }

    /**
     * Resposta de erro interno
     */
    public static function internalError(string $message = 'Erro interno do servidor'): void
    {
        self::error($message, 500);
    }

    /**
     * Resposta customizada
     */
    public static function custom(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}