<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class ExperienceValidationException extends Exception
{
    public function __construct(private readonly array $errors)
    {
        parent::__construct("Erro de validação nos dados da experiência.", 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
