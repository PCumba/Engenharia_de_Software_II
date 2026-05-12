<?php
/**
 * Classe para Validação de Dados
 */

class Validator {
    public static function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach (explode('|', $fieldRules) as $rule) {
                $rule = trim($rule);
                
                if ($rule === 'required' && empty($value)) {
                    $errors[$field][] = "$field é obrigatório";
                }
                
                if (strpos($rule, 'min:') === 0) {
                    $min = (int)substr($rule, 4);
                    if (!empty($value) && strlen($value) < $min) {
                        $errors[$field][] = "$field deve ter no mínimo $min caracteres";
                    }
                }
                
                if (strpos($rule, 'max:') === 0) {
                    $max = (int)substr($rule, 4);
                    if (!empty($value) && strlen($value) > $max) {
                        $errors[$field][] = "$field deve ter no máximo $max caracteres";
                    }
                }
                
                if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "$field deve ser um email válido";
                }
                
                if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $errors[$field][] = "$field deve ser um número";
                }
                
                if ($rule === 'password_complexity' && !empty($value)) {
                    if (!self::validatePasswordComplexity($value)) {
                        $errors[$field][] = "$field deve ter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e caracteres especiais";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validar complexidade da senha
     * Requirements: 1.6, 8.5
     * @param string $password Senha a ser validada
     * @return bool
     */
    public static function validatePasswordComplexity($password) {
        // Mínimo 8 caracteres
        if (strlen($password) < 8) {
            return false;
        }

        // Deve conter pelo menos uma letra minúscula
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Deve conter pelo menos uma letra maiúscula
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Deve conter pelo menos um número
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Deve conter pelo menos um caractere especial
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    public static function isValid($errors) {
        return count($errors) === 0;
    }
}
?>
