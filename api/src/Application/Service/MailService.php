<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Contract\MailerInterface;

class MailService
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetLink): bool
    {

        $subject = "Redefinição de Senha";

        $content = "
            <p>Olá, <strong>{$toName}</strong>.</p>
            <p>Recebemos uma solicitação para redefinir a senha da sua conta. Se você realizou esta solicitação, clique no botão abaixo para criar uma nova senha:</p>
            <div style='text-align:center; margin:35px 0;'>
                <a href='{$resetLink}' style='display:inline-block; padding:14px 32px; background-color:#01c38d; color:#ffffff; text-decoration:none; border-radius:8px; font-weight:700; font-size:16px;'>Redefinir minha senha</a>
            </div>
            <p style='font-size: 14px; color: #718096;'>Caso você não tenha solicitado esta alteração, por favor, ignore este e-mail com segurança. Este link de redefinição expirará em breve.</p>
        ";

        return $this->mailer->send($toEmail, $toName, $subject, $content);

    }

    public function sendOtp(string $toEmail, string $toName, string $otp): bool
    {
        $subject = "Seu Código de Verificação";

        $content = "
            <p>Olá, <strong>{$toName}</strong>.</p>
            <p>Para concluir sua autenticação, utilize o código de verificação de uso único abaixo:</p>
            <div style='text-align:center; margin:35px 0;'>
                <span style='display:inline-block; padding:15px 35px; background-color:#f8fafc; color:#01c38d; font-size: 36px; font-family: \"Courier New\", Courier, monospace; border-radius:8px; font-weight:bold; letter-spacing: 8px; border: 1px dashed #cbd5e0;'>{$otp}</span>
            </div>
            <p style='font-size: 14px; color: #718096;'>Este código é válido por <strong>10 minutos</strong>. Se você não tentou realizar o acesso, por favor, desconsidere esta mensagem.</p>
        ";

        return $this->mailer->send($toEmail, $toName, $subject, $content);
    }

    public function sendSignupConfirmation(string $toEmail, string $toName, string $welcomeLink): bool
    {

        $subject = "Bem-vindo à plataforma!";

        $content = "
            <p>Olá, <strong>{$toName}</strong>.</p>
            <p>É um prazer ter você conosco! Seu cadastro foi concluído com sucesso e agora você já pode explorar todas as funcionalidades do sistema.</p>
            <div style='text-align:center; margin:35px 0;'>
                <a href='{$welcomeLink}' style='display:inline-block; padding:14px 32px; background-color:#01c38d; color:#ffffff; text-decoration:none; border-radius:8px; font-weight:700; font-size:16px;'>Acessar minha conta</a>
            </div>
            <p>Se tiver qualquer dúvida ou precisar de auxílio, não hesite em entrar em contato.</p>
        ";

        return $this->mailer->send($toEmail, $toName, $subject, $content);

    }
}
