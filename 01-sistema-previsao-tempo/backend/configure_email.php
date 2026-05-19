<?php
/**
 * Script para Configurar Email
 */

echo "=== CONFIGURAÇÃO DE EMAIL ===\n\n";

$envFile = __DIR__ . '/src/config/.env';

if (!file_exists($envFile)) {
    echo "✗ Ficheiro .env não encontrado!\n";
    exit(1);
}

echo "Este script irá ajudar a configurar o email corretamente.\n\n";

echo "OPÇÕES:\n";
echo "1. Configurar Gmail com App Password\n";
echo "2. Configurar outro provedor SMTP\n";
echo "3. Usar modo simulação (desenvolvimento)\n";
echo "4. Testar configuração atual\n";

$choice = readline("Escolha uma opção (1-4): ");

switch ($choice) {
    case '1':
        configureGmail();
        break;
    case '2':
        configureCustomSMTP();
        break;
    case '3':
        enableSimulationMode();
        break;
    case '4':
        testCurrentConfig();
        break;
    default:
        echo "Opção inválida!\n";
        exit(1);
}

function configureGmail() {
    echo "\n=== CONFIGURAÇÃO DO GMAIL ===\n";
    echo "Para usar Gmail, precisa de:\n";
    echo "1. Ativar autenticação de 2 fatores na sua conta Google\n";
    echo "2. Gerar uma 'App Password' específica para esta aplicação\n";
    echo "3. Usar essa App Password em vez da sua senha normal\n\n";
    
    echo "Passos para gerar App Password:\n";
    echo "1. Vá para https://myaccount.google.com/security\n";
    echo "2. Em 'Signing in to Google', clique em 'App passwords'\n";
    echo "3. Selecione 'Mail' e 'Other (custom name)'\n";
    echo "4. Digite 'Weather App' como nome\n";
    echo "5. Copie a password gerada (16 caracteres)\n\n";
    
    $email = readline("Digite o seu email Gmail: ");
    $appPassword = readline("Digite a App Password (16 caracteres): ");
    
    if (strlen($appPassword) !== 16) {
        echo "⚠️ App Password deve ter exatamente 16 caracteres!\n";
        return;
    }
    
    updateEnvFile([
        'MAIL_HOST' => 'smtp.gmail.com',
        'MAIL_PORT' => '587',
        'MAIL_USER' => $email,
        'MAIL_PASSWORD' => $appPassword,
        'MAIL_ENCRYPTION' => 'tls',
        'FROM_NAME' => 'Weather App'
    ]);
    
    echo "✓ Configuração do Gmail atualizada!\n";
    testEmailConfig();
}

function configureCustomSMTP() {
    echo "\n=== CONFIGURAÇÃO SMTP PERSONALIZADA ===\n";
    
    $host = readline("SMTP Host: ");
    $port = readline("SMTP Port (587): ") ?: '587';
    $user = readline("SMTP Username: ");
    $password = readline("SMTP Password: ");
    $encryption = readline("Encryption (tls/ssl): ") ?: 'tls';
    
    updateEnvFile([
        'MAIL_HOST' => $host,
        'MAIL_PORT' => $port,
        'MAIL_USER' => $user,
        'MAIL_PASSWORD' => $password,
        'MAIL_ENCRYPTION' => $encryption,
        'FROM_NAME' => 'Weather App'
    ]);
    
    echo "✓ Configuração SMTP atualizada!\n";
    testEmailConfig();
}

function enableSimulationMode() {
    echo "\n=== MODO SIMULAÇÃO ===\n";
    echo "No modo simulação, os emails não são enviados realmente.\n";
    echo "Em vez disso, aparecem nos logs do servidor.\n\n";
    
    updateEnvFile([
        'APP_ENV' => 'development'
    ]);
    
    echo "✓ Modo simulação ativado!\n";
    echo "Os emails aparecerão nos logs em vez de serem enviados.\n";
}

function testCurrentConfig() {
    echo "\n=== TESTE DA CONFIGURAÇÃO ATUAL ===\n";
    
    // Carregar .env
    $env = parse_ini_file(__DIR__ . '/src/config/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
    
    echo "Configurações atuais:\n";
    echo "MAIL_HOST: " . getenv('MAIL_HOST') . "\n";
    echo "MAIL_PORT: " . getenv('MAIL_PORT') . "\n";
    echo "MAIL_USER: " . getenv('MAIL_USER') . "\n";
    echo "APP_ENV: " . (getenv('APP_ENV') ?: 'production') . "\n\n";
    
    testEmailConfig();
}

function updateEnvFile($updates) {
    $envFile = __DIR__ . '/src/config/.env';
    $content = file_get_contents($envFile);
    
    foreach ($updates as $key => $value) {
        $pattern = "/^$key=.*$/m";
        $replacement = "$key=$value";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            $content .= "\n$replacement";
        }
    }
    
    file_put_contents($envFile, $content);
}

function testEmailConfig() {
    echo "\n=== TESTE DE ENVIO ===\n";
    
    $testEmail = readline("Digite um email para teste (ou Enter para pular): ");
    
    if (empty($testEmail)) {
        echo "Teste de envio ignorado.\n";
        return;
    }
    
    // Incluir dependências
    require_once __DIR__ . '/src/config/database.php';
    require_once __DIR__ . '/src/services/EmailService.php';
    
    // Carregar .env
    $env = parse_ini_file(__DIR__ . '/src/config/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
    
    try {
        $emailService = new EmailService();
        
        $testUser = [
            'name' => 'Utilizador Teste',
            'email' => $testEmail
        ];
        
        $result = $emailService->sendWelcomeEmail($testUser);
        
        if ($result) {
            echo "✓ Email de teste enviado com sucesso!\n";
            echo "Verifique a caixa de entrada de $testEmail\n";
        } else {
            echo "✗ Falha ao enviar email de teste\n";
            echo "Verifique os logs para mais detalhes\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Erro ao testar email: " . $e->getMessage() . "\n";
    }
}

echo "\n=== CONFIGURAÇÃO CONCLUÍDA ===\n";
echo "Para testar o sistema completo, execute:\n";
echo "php test_auth.php\n";
echo "php test_api.php\n";
?>