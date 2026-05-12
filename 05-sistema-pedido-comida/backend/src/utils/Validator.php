<?php
/**
 * Motor de Validação com Regras Customizáveis
 */

class Validator {
    private static $rules = [
        'required' => 'required',
        'email' => 'email',
        'numeric' => 'numeric',
        'min' => 'min',
        'max' => 'max'
    ];

    public static function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $rulesList = explode('|', $fieldRules);
            
            foreach ($rulesList as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;

                if (!self::checkRule($field, $data[$field] ?? null, $ruleName, $ruleValue)) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = self::getErrorMessage($ruleName, $ruleValue);
                }
            }
        }

        return $errors;
    }

    private static function checkRule($field, $value, $rule, $ruleValue) {
        switch ($rule) {
            case 'required':
                return !empty($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'numeric':
                return is_numeric($value);
            case 'min':
                return strlen($value) >= (int)$ruleValue;
            case 'max':
                return strlen($value) <= (int)$ruleValue;
            default:
                return true;
        }
    }

    private static function getErrorMessage($rule, $value) {
        switch ($rule) {
            case 'required':
                return 'Campo obrigatório';
            case 'email':
                return 'Email inválido';
            case 'numeric':
                return 'Deve ser numérico';
            case 'min':
                return "Mínimo {$value} caracteres";
            case 'max':
                return "Máximo {$value} caracteres";
            default:
                return 'Erro de validação';
        }
    }

    public static function isValid($errors) {
        return empty($errors);
    }
}
?>
