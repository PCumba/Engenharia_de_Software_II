<?php
/**
 * Serviço de Email
 * Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6, 12.7
 */

class EmailService {
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        // Configurações SMTP - devem vir do arquivo de configuração
        $this->smtpHost = getenv('MAIL_HOST') ?: 'localhost';
        $this->smtpPort = getenv('MAIL_PORT') ?: 587;
        $this->smtpUsername = getenv('MAIL_USER') ?: '';
        $this->smtpPassword = getenv('MAIL_PASSWORD') ?: '';
        $this->fromEmail = getenv('MAIL_USER') ?: 'noreply@weatherapp.com';
        $this->fromName = getenv('FROM_NAME') ?: 'Weather App';
    }

    /**
     * Enviar email de redefinição de senha
     * Requirements: 12.3
     * @param array $user Dados do usuário
     * @param string $resetToken Token de redefinição
     * @return bool
     */
    public function sendPasswordResetEmail($user, $resetToken) {
        $resetUrl = $this->getResetUrl($resetToken);
        
        $subject = 'Redefinição de Senha - Weather App';
        
        $htmlBody = $this->getPasswordResetTemplate($user['name'], $resetUrl, 'html');
        $textBody = $this->getPasswordResetTemplate($user['name'], $resetUrl, 'text');

        return $this->sendEmail($user['email'], $subject, $htmlBody, $textBody);
    }

    /**
     * Enviar email de boas-vindas
     * Requirements: 12.2
     * @param array $user Dados do usuário
     * @return bool
     */
    public function sendWelcomeEmail($user) {
        $subject = 'Bem-vindo ao Weather App!';
        
        $htmlBody = $this->getWelcomeTemplate($user['name'], 'html');
        $textBody = $this->getWelcomeTemplate($user['name'], 'text');

        return $this->sendEmail($user['email'], $subject, $htmlBody, $textBody);
    }

    /**
     * Enviar notificação de alteração de senha
     * Requirements: 12.4
     * @param array $user Dados do usuário
     * @return bool
     */
    public function sendPasswordChangedNotification($user) {
        $subject = 'Senha Alterada - Weather App';
        
        $htmlBody = $this->getPasswordChangedTemplate($user['name'], 'html');
        $textBody = $this->getPasswordChangedTemplate($user['name'], 'text');

        return $this->sendEmail($user['email'], $subject, $htmlBody, $textBody);
    }

    /**
     * Enviar confirmação de exclusão de conta
     * Requirements: 12.4
     * @param array $user Dados do usuário
     * @return bool
     */
    public function sendAccountDeletionConfirmation($user) {
        $subject = 'Conta Excluída - Weather App';
        
        $htmlBody = $this->getAccountDeletionTemplate($user['name'], 'html');
        $textBody = $this->getAccountDeletionTemplate($user['name'], 'text');

        return $this->sendEmail($user['email'], $subject, $htmlBody, $textBody);
    }

    /**
     * Enviar notificação de atualização de perfil
     * Requirements: 12.4
     * @param array $user Dados do usuário
     * @param array $changes Alterações realizadas
     * @return bool
     */
    public function sendProfileUpdateNotification($user, $changes) {
        $subject = 'Perfil Atualizado - Weather App';
        
        $htmlBody = $this->getProfileUpdateTemplate($user['name'], $changes, 'html');
        $textBody = $this->getProfileUpdateTemplate($user['name'], $changes, 'text');

        return $this->sendEmail($user['email'], $subject, $htmlBody, $textBody);
    }

    /**
     * Enviar email com retry logic
     * Requirements: 12.7
     * @param string $to Email de destino
     * @param string $subject Assunto
     * @param string $htmlBody Corpo HTML
     * @param string $textBody Corpo texto
     * @param int $maxRetries Máximo de tentativas
     * @return bool
     */
    private function sendEmail($to, $subject, $htmlBody, $textBody, $maxRetries = 3) {
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                $result = $this->sendEmailAttempt($to, $subject, $htmlBody, $textBody);
                
                if ($result) {
                    return true;
                }
                
                $attempt++;
                
                if ($attempt < $maxRetries) {
                    // Aguardar antes da próxima tentativa (exponential backoff)
                    sleep(pow(2, $attempt));
                }
            } catch (Exception $e) {
                error_log("Erro ao enviar email (tentativa $attempt): " . $e->getMessage());
                $attempt++;
                
                if ($attempt < $maxRetries) {
                    sleep(pow(2, $attempt));
                }
            }
        }
        
        return false;
    }

    /**
     * Tentativa de envio de email
     * @param string $to Email de destino
     * @param string $subject Assunto
     * @param string $htmlBody Corpo HTML
     * @param string $textBody Corpo texto
     * @return bool
     */
    private function sendEmailAttempt($to, $subject, $htmlBody, $textBody) {
        // Para desenvolvimento, simular envio
        if ((getenv('APP_ENV') ?: 'development') === 'development') {
            error_log("EMAIL SIMULADO - Para: $to, Assunto: $subject");
            return true;
        }

        // Usar SMTP para envio real
        return $this->sendViaSMTP($to, $subject, $htmlBody, $textBody);
    }

    /**
     * Enviar email via SMTP
     * @param string $to Email de destino
     * @param string $subject Assunto
     * @param string $htmlBody Corpo HTML
     * @param string $textBody Corpo texto
     * @return bool
     */
    private function sendViaSMTP($to, $subject, $htmlBody, $textBody) {
        try {
            // Conectar ao servidor SMTP
            $socket = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
            
            if (!$socket) {
                error_log("Erro SMTP: Não foi possível conectar ao servidor");
                return false;
            }

            // Ler resposta inicial
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '220') {
                error_log("Erro SMTP: Resposta inicial inválida - $response");
                fclose($socket);
                return false;
            }

            // EHLO
            fputs($socket, "EHLO " . $this->smtpHost . "\r\n");
            $response = fgets($socket, 512);

            // STARTTLS
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '220') {
                error_log("Erro SMTP: STARTTLS falhou - $response");
                fclose($socket);
                return false;
            }

            // Upgrade para TLS
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log("Erro SMTP: Falha ao ativar TLS");
                fclose($socket);
                return false;
            }

            // EHLO novamente após TLS
            fputs($socket, "EHLO " . $this->smtpHost . "\r\n");
            $response = fgets($socket, 512);

            // AUTH LOGIN
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '334') {
                error_log("Erro SMTP: AUTH LOGIN falhou - $response");
                fclose($socket);
                return false;
            }

            // Username
            fputs($socket, base64_encode($this->smtpUsername) . "\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '334') {
                error_log("Erro SMTP: Username rejeitado - $response");
                fclose($socket);
                return false;
            }

            // Password
            fputs($socket, base64_encode($this->smtpPassword) . "\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '235') {
                error_log("Erro SMTP: Autenticação falhou - $response");
                fclose($socket);
                return false;
            }

            // MAIL FROM
            fputs($socket, "MAIL FROM: <" . $this->fromEmail . ">\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '250') {
                error_log("Erro SMTP: MAIL FROM rejeitado - $response");
                fclose($socket);
                return false;
            }

            // RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '250') {
                error_log("Erro SMTP: RCPT TO rejeitado - $response");
                fclose($socket);
                return false;
            }

            // DATA
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '354') {
                error_log("Erro SMTP: DATA rejeitado - $response");
                fclose($socket);
                return false;
            }

            // Headers e corpo do email
            $boundary = 'boundary-' . uniqid();
            
            $headers = "From: " . $this->fromName . " <" . $this->fromEmail . ">\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
            $headers .= "\r\n";

            $body = "--$boundary\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $textBody . "\r\n\r\n";
            
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $htmlBody . "\r\n\r\n";
            
            $body .= "--$boundary--\r\n";

            fputs($socket, $headers . $body . "\r\n.\r\n");
            $response = fgets($socket, 512);
            
            if (substr($response, 0, 3) != '250') {
                error_log("Erro SMTP: Envio rejeitado - $response");
                fclose($socket);
                return false;
            }

            // QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);

            return true;

        } catch (Exception $e) {
            error_log("Erro SMTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Template de redefinição de senha
     * @param string $name Nome do usuário
     * @param string $resetUrl URL de redefinição
     * @param string $format Formato (html ou text)
     * @return string
     */
    private function getPasswordResetTemplate($name, $resetUrl, $format) {
        if ($format === 'html') {
            return "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2196F3;'>Redefinição de Senha</h2>
                    <p>Olá, $name!</p>
                    <p>Você solicitou a redefinição de sua senha no Weather App.</p>
                    <p>Clique no link abaixo para redefinir sua senha:</p>
                    <p><a href='$resetUrl' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Redefinir Senha</a></p>
                    <p>Este link expira em 1 hora.</p>
                    <p>Se você não solicitou esta redefinição, ignore este email.</p>
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #666;'>Weather App - Sistema de Previsão do Tempo</p>
                </div>
            </body>
            </html>";
        } else {
            return "
Redefinição de Senha - Weather App

Olá, $name!

Você solicitou a redefinição de sua senha no Weather App.

Acesse o link abaixo para redefinir sua senha:
$resetUrl

Este link expira em 1 hora.

Se você não solicitou esta redefinição, ignore este email.

---
Weather App - Sistema de Previsão do Tempo";
        }
    }

    /**
     * Template de boas-vindas
     * @param string $name Nome do usuário
     * @param string $format Formato (html ou text)
     * @return string
     */
    private function getWelcomeTemplate($name, $format) {
        if ($format === 'html') {
            return "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2196F3;'>Bem-vindo ao Weather App!</h2>
                    <p>Olá, $name!</p>
                    <p>Sua conta foi criada com sucesso no Weather App.</p>
                    <p>Agora você pode:</p>
                    <ul>
                        <li>Consultar previsões do tempo em tempo real</li>
                        <li>Salvar suas cidades favoritas</li>
                        <li>Exportar seus dados meteorológicos</li>
                        <li>Personalizar sua experiência com temas e idiomas</li>
                    </ul>
                    <p>Aproveite nossa plataforma!</p>
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #666;'>Weather App - Sistema de Previsão do Tempo</p>
                </div>
            </body>
            </html>";
        } else {
            return "
Bem-vindo ao Weather App!

Olá, $name!

Sua conta foi criada com sucesso no Weather App.

Agora você pode:
- Consultar previsões do tempo em tempo real
- Salvar suas cidades favoritas
- Exportar seus dados meteorológicos
- Personalizar sua experiência com temas e idiomas

Aproveite nossa plataforma!

---
Weather App - Sistema de Previsão do Tempo";
        }
    }

    /**
     * Template de senha alterada
     * @param string $name Nome do usuário
     * @param string $format Formato (html ou text)
     * @return string
     */
    private function getPasswordChangedTemplate($name, $format) {
        if ($format === 'html') {
            return "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2196F3;'>Senha Alterada</h2>
                    <p>Olá, $name!</p>
                    <p>Sua senha foi alterada com sucesso no Weather App.</p>
                    <p>Se você não fez esta alteração, entre em contato conosco imediatamente.</p>
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #666;'>Weather App - Sistema de Previsão do Tempo</p>
                </div>
            </body>
            </html>";
        } else {
            return "
Senha Alterada - Weather App

Olá, $name!

Sua senha foi alterada com sucesso no Weather App.

Se você não fez esta alteração, entre em contato conosco imediatamente.

---
Weather App - Sistema de Previsão do Tempo";
        }
    }

    /**
     * Template de conta excluída
     * @param string $name Nome do usuário
     * @param string $format Formato (html ou text)
     * @return string
     */
    private function getAccountDeletionTemplate($name, $format) {
        if ($format === 'html') {
            return "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2196F3;'>Conta Excluída</h2>
                    <p>Olá, $name!</p>
                    <p>Sua conta no Weather App foi excluída conforme solicitado.</p>
                    <p>Todos os seus dados foram removidos permanentemente.</p>
                    <p>Obrigado por ter usado nossos serviços!</p>
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #666;'>Weather App - Sistema de Previsão do Tempo</p>
                </div>
            </body>
            </html>";
        } else {
            return "
Conta Excluída - Weather App

Olá, $name!

Sua conta no Weather App foi excluída conforme solicitado.

Todos os seus dados foram removidos permanentemente.

Obrigado por ter usado nossos serviços!

---
Weather App - Sistema de Previsão do Tempo";
        }
    }

    /**
     * Template de perfil atualizado
     * @param string $name Nome do usuário
     * @param array $changes Alterações realizadas
     * @param string $format Formato (html ou text)
     * @return string
     */
    private function getProfileUpdateTemplate($name, $changes, $format) {
        $changesList = implode(', ', array_keys($changes));
        
        if ($format === 'html') {
            return "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2196F3;'>Perfil Atualizado</h2>
                    <p>Olá, $name!</p>
                    <p>Seu perfil no Weather App foi atualizado.</p>
                    <p>Campos alterados: $changesList</p>
                    <p>Se você não fez essas alterações, entre em contato conosco.</p>
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #666;'>Weather App - Sistema de Previsão do Tempo</p>
                </div>
            </body>
            </html>";
        } else {
            return "
Perfil Atualizado - Weather App

Olá, $name!

Seu perfil no Weather App foi atualizado.

Campos alterados: $changesList

Se você não fez essas alterações, entre em contato conosco.

---
Weather App - Sistema de Previsão do Tempo";
        }
    }

    /**
     * Gerar URL de redefinição de senha
     * @param string $token Token de redefinição
     * @return string
     */
    private function getResetUrl($token) {
        $baseUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:4200';
        return $baseUrl . '/auth/reset-password?token=' . urlencode($token);
    }
}
?>