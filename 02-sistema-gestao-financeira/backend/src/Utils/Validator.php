<?php

namespace App\Utils;

/**
 * Classe para validação de dados
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Verificar se campos são obrigatórios
     */
    public function required(array $fields): self
    {
        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
                $this->errors[$field][] = "O campo {$field} é obrigatório";
            }
        }
        return $this;
    }

    /**
     * Validar email
     */
    public function email(string $field): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "O campo {$field} deve ser um email válido";
        }
        return $this;
    }

    /**
     * Validar comprimento mínimo
     */
    public function minLength(string $field, int $length): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field][] = "O campo {$field} deve ter pelo menos {$length} caracteres";
        }
        return $this;
    }

    /**
     * Validar comprimento máximo
     */
    public function maxLength(string $field, int $length): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field][] = "O campo {$field} deve ter no máximo {$length} caracteres";
        }
        return $this;
    }

    /**
     * Validar se é número
     */
    public function numeric(string $field): self
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = "O campo {$field} deve ser um número";
        }
        return $this;
    }

    /**
     * Validar se é inteiro
     */
    public function integer(string $field): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = "O campo {$field} deve ser um número inteiro";
        }
        return $this;
    }

    /**
     * Validar valor mínimo
     */
    public function min(string $field, float $min): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && $this->data[$field] < $min) {
            $this->errors[$field][] = "O campo {$field} deve ser maior ou igual a {$min}";
        }
        return $this;
    }

    /**
     * Validar valor máximo
     */
    public function max(string $field, float $max): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && $this->data[$field] > $max) {
            $this->errors[$field][] = "O campo {$field} deve ser menor ou igual a {$max}";
        }
        return $this;
    }

    /**
     * Validar se está em uma lista de valores
     */
    public function in(string $field, array $values): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $valuesList = implode(', ', $values);
            $this->errors[$field][] = "O campo {$field} deve ser um dos valores: {$valuesList}";
        }
        return $this;
    }

    /**
     * Validar data
     */
    public function date(string $field, string $format = 'Y-m-d'): self
    {
        if (isset($this->data[$field])) {
            $date = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field][] = "O campo {$field} deve ser uma data válida no formato {$format}";
            }
        }
        return $this;
    }

    /**
     * Validar URL
     */
    public function url(string $field): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = "O campo {$field} deve ser uma URL válida";
        }
        return $this;
    }

    /**
     * Validar regex
     */
    public function regex(string $field, string $pattern): self
    {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field][] = "O campo {$field} não atende ao formato exigido";
        }
        return $this;
    }

    /**
     * Validar se é array
     */
    public function array(string $field): self
    {
        if (isset($this->data[$field]) && !is_array($this->data[$field])) {
            $this->errors[$field][] = "O campo {$field} deve ser um array";
        }
        return $this;
    }

    /**
     * Validar se é booleano
     */
    public function boolean(string $field): self
    {
        if (isset($this->data[$field]) && !is_bool($this->data[$field])) {
            $this->errors[$field][] = "O campo {$field} deve ser verdadeiro ou falso";
        }
        return $this;
    }

    /**
     * Validar confirmação de campo (ex: password_confirmation)
     */
    public function confirmed(string $field): self
    {
        $confirmationField = $field . '_confirmation';
        if (isset($this->data[$field]) && isset($this->data[$confirmationField])) {
            if ($this->data[$field] !== $this->data[$confirmationField]) {
                $this->errors[$field][] = "A confirmação do campo {$field} não confere";
            }
        }
        return $this;
    }

    /**
     * Validar se campo é único (callback personalizado)
     */
    public function unique(string $field, callable $callback): self
    {
        if (isset($this->data[$field])) {
            if (!$callback($this->data[$field])) {
                $this->errors[$field][] = "O valor do campo {$field} já está em uso";
            }
        }
        return $this;
    }

    /**
     * Validação customizada
     */
    public function custom(string $field, callable $callback, string $message = null): self
    {
        if (isset($this->data[$field])) {
            if (!$callback($this->data[$field])) {
                $defaultMessage = "O campo {$field} é inválido";
                $this->errors[$field][] = $message ?? $defaultMessage;
            }
        }
        return $this;
    }

    /**
     * Verificar se a validação passou
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Obter erros de validação
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obter primeiro erro de um campo
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Obter todos os erros como array simples
     */
    public function getErrorMessages(): array
    {
        $messages = [];
        foreach ($this->errors as $field => $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return $messages;
    }

    /**
     * Adicionar erro manualmente
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    /**
     * Limpar erros
     */
    public function clearErrors(): self
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Validar múltiplos campos com as mesmas regras
     */
    public function fields(array $fields, callable $validator): self
    {
        foreach ($fields as $field) {
            $validator($this, $field);
        }
        return $this;
    }

    /**
     * Validação condicional
     */
    public function when(string $field, $value, callable $validator): self
    {
        if (isset($this->data[$field]) && $this->data[$field] === $value) {
            $validator($this);
        }
        return $this;
    }

    /**
     * Validar apenas se o campo estiver presente
     */
    public function sometimes(string $field, callable $validator): self
    {
        if (isset($this->data[$field])) {
            $validator($this, $field);
        }
        return $this;
    }
}