<?php
/**
 * Configurações gerais da aplicação
 */

return [
    'app' => [
        'name' => 'Sistema de Gestão Financeira',
        'version' => '1.0.0',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
        'timezone' => 'America/Sao_Paulo',
    ],
    
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production',
        'algorithm' => 'HS256',
        'expiration' => 3600, // 1 hora
        'refresh_expiration' => 604800, // 7 dias
    ],
    
    'security' => [
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutos
        'session_lifetime' => 3600, // 1 hora
    ],
    
    'pagination' => [
        'default_limit' => 20,
        'max_limit' => 100,
    ],
    
    'upload' => [
        'max_file_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'csv', 'xlsx'],
        'upload_path' => __DIR__ . '/../../uploads/',
    ],
    
    'email' => [
        'smtp_host' => $_ENV['SMTP_HOST'] ?? 'localhost',
        'smtp_port' => $_ENV['SMTP_PORT'] ?? 587,
        'smtp_username' => $_ENV['SMTP_USERNAME'] ?? '',
        'smtp_password' => $_ENV['SMTP_PASSWORD'] ?? '',
        'smtp_encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
        'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@financeiro.com',
        'from_name' => $_ENV['FROM_NAME'] ?? 'Sistema Financeiro',
    ],
    
    'cache' => [
        'default_ttl' => 3600, // 1 hora
        'enabled' => $_ENV['CACHE_ENABLED'] ?? true,
    ],
    
    'backup' => [
        'enabled' => $_ENV['BACKUP_ENABLED'] ?? true,
        'frequency' => 'daily',
        'retention_days' => 30,
        'path' => __DIR__ . '/../../backups/',
    ],
    
    'api' => [
        'rate_limit' => [
            'enabled' => true,
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000,
        ],
        'cors' => [
            'allowed_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:4200'),
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        ],
    ],
    
    'logging' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        'path' => __DIR__ . '/../../logs/',
        'max_files' => 30,
    ],
];