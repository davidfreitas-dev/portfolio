<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Contract\MailerInterface;

class MailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {

        $this->mailer = $mailer;

    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetLink): bool
    {

        $subject = "Redefinição de senha";

        $content = "
      <p>Olá <strong>{$toName}</strong>,</p>
      <p>Recebemos uma solicitação para redefinir sua senha. Clique no botão abaixo para continuar:</p>
      <div style='text-align:center; margin:30px 0;'>
        <a href='{$resetLink}' style='display:inline-block; padding:12px 28px; background:#038de7; color:#fff; text-decoration:none; border-radius:12px; font-weight:bold;'>Redefir Senha</a>
      </div>
      <p>Se você não solicitou essa alteração, pode ignorar este e-mail.</p>
    ";

        return $this->mailer->send($toEmail, $toName, $subject, $content);

    }

    public function sendOtp(string $toEmail, string $toName, string $otp): bool
    {
        $subject = "Seu código de acesso";

        $content = "
      <p>Olá <strong>{$toName}</strong>,</p>
      <p>Utilize o código abaixo para realizar o seu acesso:</p>
      <div style='text-align:center; margin:30px 0;'>
        <span style='display:inline-block; padding:12px 28px; background:#f4f4f4; color:#333; font-size: 24px; font-family: monospace; border-radius:12px; font-weight:bold; letter-spacing: 5px;'>{$otp}</span>
      </div>
      <p>Este código expira em 10 minutos.</p>
      <p>Se você não solicitou este código, ignore este e-mail.</p>
    ";

        return $this->mailer->send($toEmail, $toName, $subject, $content);
    }

    public function sendSignupConfirmation(string $toEmail, string $toName, string $welcomeLink): bool
    {

        $subject = "Bem-vindo!";

        $content = "
      <p>Olá <strong>{$toName}</strong>,</p>
      <p>Seu cadastro foi realizado com sucesso. Clique no link abaixo para acessar sua conta:</p>
      <div style='text-align:center; margin:30px 0;'>
        <a href='{$welcomeLink}' style='display:inline-block; padding:12px 28px; background:#038de7; color:#fff; text-decoration:none; border-radius:12px; font-weight:bold;'>Acessar Conta</a>
      </div>
    ";

        return $this->mailer->send($toEmail, $toName, $subject, $content);

    }
}
