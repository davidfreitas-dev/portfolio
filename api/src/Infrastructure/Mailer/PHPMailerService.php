<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailer;

use App\Domain\Contract\MailerInterface;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use Throwable;

class PHPMailerService implements MailerInterface
{
    private PHPMailer $mailer;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly array $config,
    ) {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    public function send(string $toEmail, string $toName, string $subject, string $contentHtml): bool
    {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $this->renderTemplate($subject, $contentHtml);
            $this->mailer->AltBody = strip_tags($contentHtml);

            $this->mailer->send();
            $this->logger->info(sprintf("E-mail '%s' enviado com sucesso para %s", $subject, $toEmail));

            return true;
        } catch (PHPMailerException $e) {
            $this->logger->error(sprintf("Falha ao enviar e-mail '%s' para %s: %s", $subject, $toEmail, $this->mailer->ErrorInfo));
            throw new \Exception("Erro ao enviar e-mail: " . $this->mailer->ErrorInfo, HTTPStatus::SERVICE_UNAVAILABLE);
        } catch (Throwable $e) {
            $this->logger->error(sprintf("Erro inesperado ao enviar e-mail '%s' para %s: %s", $subject, $toEmail, $e->getMessage()));
            throw new \Exception("Ocorreu um erro inesperado ao enviar e-mail.", HTTPStatus::INTERNAL_SERVER_ERROR);
        }
    }

    private function configureMailer(): void
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['host'] ?? '';
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config['username'] ?? '';
            $this->mailer->Password   = $this->config['password'] ?? '';
            $this->mailer->SMTPSecure = $this->config['smtp_secure'] ?? 'tls';
            $this->mailer->Port       = (int)($this->config['port'] ?? 587);

            $this->mailer->setFrom(
                $this->config['from_email'] ?? '',
                $this->config['from_name'] ?? '',
            );

            $this->mailer->isHTML(true);
            $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;

            // Extra security/compatibility options
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];
        } catch (PHPMailerException $e) {
            $this->logger->error('Falha ao configurar PHPMailer: ' . $e->getMessage());
            throw new \Exception("Erro de configuração do Mailer: " . $e->getMessage(), HTTPStatus::INTERNAL_SERVER_ERROR);
        }
    }

    private function renderTemplate(string $subject, string $contentHtml): string
    {
        $title = $subject;
        $content = $contentHtml;

        $templatePath = __DIR__ . '/../../../res/views/emails/default.php';

        if (!file_exists($templatePath)) {
            $this->logger->error('Template de e-mail não encontrado: ' . $templatePath);
            return $contentHtml;
        }

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
