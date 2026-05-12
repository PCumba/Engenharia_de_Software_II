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
            }
        }

        return $errors;
    }

    public static function isValid($errors) {
        return count($errors) === 0;
    }
}
?>
