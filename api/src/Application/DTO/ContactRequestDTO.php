<?php

declare(strict_types=1);

namespace App\Application\DTO;

class ContactRequestDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $subject,
        public readonly string $message,
        public readonly string $website = '',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string)($data['name'] ?? ''),
            (string)($data['email'] ?? ''),
            (string)($data['subject'] ?? ''),
            (string)($data['message'] ?? ''),
            (string)($data['website'] ?? ''),
        );
    }

    public function validate(): array
    {
        $errors = [];

        // Honeypot check
        if (!empty($this->website)) {
            $errors['website'] = 'Bot detectado.';
        }

        if (empty($this->name)) {
            $errors['name'] = 'O nome é obrigatório.';
        }

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Um e-mail válido é obrigatório.';
        }

        if (empty($this->subject)) {
            $errors['subject'] = 'O assunto é obrigatório.';
        }

        if (empty($this->message)) {
            $errors['message'] = 'A mensagem é obrigatória.';
        }

        return $errors;
    }
}
