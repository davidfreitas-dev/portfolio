<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface MailerInterface
{
    public function send(string $toEmail, string $toName, string $subject, string $contentHtml): bool;
}
