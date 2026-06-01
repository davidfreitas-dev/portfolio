<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserProfileRequestDTO
{
    #[Assert\NotBlank(message: "O nome é obrigatório.")]
    public string $name;

    #[Assert\NotBlank(message: "O e-mail é obrigatório.")]
    #[Assert\Email(message: "O e-mail informado não é válido.")]
    public string $email;

    public ?string $phone = null;
}
