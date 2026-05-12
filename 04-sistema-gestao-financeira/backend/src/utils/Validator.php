<?php
/**
 * Validador de Dados
 */

class Validator {
    public static function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $fieldValue = $data[$field] ?? null;
            $ruleArray = explode('|', $rule);

            foreach ($ruleArray as $singleRule) {
                $result = self::validateField($fieldValue, $singleRule, $field);
                if ($result !== true) {
                    $errors[$field][] = $result;
                }
            }
        }

        return $errors;
    }

    private static function validateField($value, $rule, $field) {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $ruleValue) = explode(':', $rule);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }

        switch ($ruleName) {
            case 'required':
                return empty($value) ? "{$field} é obrigatório" : true;
            case 'min':
                return strlen($value) < $ruleValue ? "{$field} deve ter mínimo {$ruleValue} caracteres" : true;
            case 'max':
                return strlen($value) > $ruleValue ? "{$field} deve ter máximo {$ruleValue} caracteres" : true;
            case 'email':
                return !filter_var($value, FILTER_VALIDATE_EMAIL) ? "{$field} deve ser um email válido" : true;
            case 'numeric':
                return !is_numeric($value) ? "{$field} deve ser um número" : true;
            default:
                return true;
        }
    }

    public static function isValid($errors) {
        return empty($errors);
    }
}
?>
