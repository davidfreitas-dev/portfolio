<?php

declare(strict_types=1);

namespace App\Application\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordResetRequestDTO
{
    #[Assert\NotBlank(message: "O e-mail é obrigatório.")]
    #[Assert\Email(message: "O e-mail informado não é válido.")]
    public string $email;
}
