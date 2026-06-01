<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordRequestDTO
{
    #[Assert\NotBlank(message: "A senha atual é obrigatória.")]
    public string $current_password;

    #[Assert\NotBlank(message: "A nova senha é obrigatória.")]
    #[Assert\Length(min: 8, minMessage: "A nova senha deve ter pelo menos 8 caracteres.")]
    public string $new_password;

    #[Assert\NotBlank(message: "A confirmação da nova senha é obrigatória.")]
    #[Assert\Expression(
        "this.new_password == this.new_password_confirm",
        message: "As senhas não conferem.",
    )]
    public string $new_password_confirm;
}
