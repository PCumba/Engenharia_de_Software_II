<?php
/**
 * Bootstrap da aplicação
 * Configurações iniciais e autoloader
 */

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/error.log');

// Autoloader simples
spl_autoload_register(function ($class) {
    // Converter namespace para caminho de arquivo
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../';
    
    // Verificar se a classe usa o namespace base
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Obter o nome relativo da classe
    $relativeClass = substr($class, $len);
    
    // Substituir namespace separators por directory separators
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // Carregar o arquivo se existir
    if (file_exists($file)) {
        require $file;
    }
});

// Carregar configurações
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/app.php';

// Configurar headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Configurar Content-Type padrão
header('Content-Type: application/json; charset=utf-8');