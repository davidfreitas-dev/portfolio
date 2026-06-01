<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class ProjectValidationException extends Exception
{
    private array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct("Erro de validação nos dados do projeto.", 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
