<?php

namespace App\Services;

/**
 * Serviço de Email (implementação simplificada)
 */
class EmailService
{
    private array $config;

    public function __construct()
    {
        $appConfig = require __DIR__ . '/../config/app.php';
        $this->config = $appConfig['email'];
    }

    /**
     * Enviar email de boas-vindas
     */
    public function sendWelcomeEmail(string $to, string $name): bool
    {
        $subject = 'Bem-vindo ao Sistema Financeiro';
        $body = "
            <h2>Olá, {$name}!</h2>
            <p>Sua conta foi criada com sucesso no Sistema de Gestão Financeira.</p>
            <p>Comece agora a organizar suas finanças!</p>
        ";

        return $this->send($to, $subject, $body);
    }

    /**
     * Enviar email de recuperação de senha
     */
    public function sendPasswordResetEmail(string $to, string $name, string $token): bool
    {
        $resetUrl = ($_ENV['APP_URL'] ?? 'http://localhost:4200') . "/auth/reset-password?token={$token}&email={$to}";

        $subject = 'Recuperação de Senha - Sistema Financeiro';
        $body = "
            <h2>Olá, {$name}!</h2>
            <p>Recebemos uma solicitação de recuperação de senha para sua conta.</p>
            <p><a href='{$resetUrl}' style='padding:12px 24px;background:#3f51b5;color:white;text-decoration:none;border-radius:6px;'>Redefinir Senha</a></p>
            <p>Este link expira em 1 hora.</p>
            <p>Se você não solicitou esta alteração, ignore este email.</p>
        ";

        return $this->send($to, $subject, $body);
    }

    /**
     * Enviar email genérico
     */
    private function send(string $to, string $subject, string $body): bool
    {
        try {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                "From: {$this->config['from_name']} <{$this->config['from_email']}>",
                "Reply-To: {$this->config['from_email']}"
            ];

            // Em produção, usar SMTP. Em dev, usar mail() ou log.
            if ($_ENV['APP_ENV'] ?? 'development' === 'development') {
                error_log("EMAIL TO: {$to} | SUBJECT: {$subject}");
                return true;
            }

            return mail($to, $subject, $body, implode("\r\n", $headers));
        } catch (\Exception $e) {
            error_log("Email send error: " . $e->getMessage());
            return false;
        }
    }
}
