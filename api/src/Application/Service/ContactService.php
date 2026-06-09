<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\ContactRequestDTO;
use App\Domain\Contract\MailerInterface;
use Psr\Log\LoggerInterface;

class ContactService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly array $settings,
    ) {
    }

    public function handleContactRequest(ContactRequestDTO $dto): bool
    {
        $this->logger->info(sprintf("Processando solicitação de contato de: %s <%s>", $dto->name, $dto->email));

        $toEmail = $this->settings['from']; // Enviar para o e-mail configurado do portfólio
        $toName = $this->settings['name'];
        $subject = "Novo Contato: " . $dto->subject;

        $contentHtml = sprintf(
            "<p><strong>Nome:</strong> %s</p>" .
            "<p><strong>E-mail:</strong> %s</p>" .
            "<p><strong>Assunto:</strong> %s</p>" .
            "<p><strong>Mensagem:</strong><br>%s</p>",
            htmlspecialchars($dto->name),
            htmlspecialchars($dto->email),
            htmlspecialchars($dto->subject),
            nl2br(htmlspecialchars($dto->message))
        );

        return $this->mailer->send($toEmail, $toName, $subject, $contentHtml);
    }
}
