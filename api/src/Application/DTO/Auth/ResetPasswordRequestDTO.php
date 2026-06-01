<?php

declare(strict_types=1);

namespace App\Application\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordRequestDTO
{
    #[Assert\NotBlank(message: "O e-mail é obrigatório.")]
    #[Assert\Email(message: "O e-mail informado não é válido.")]
    public string $email;

    #[Assert\NotBlank(message: "O código é obrigatório.")]
    public string $code;

    #[Assert\NotBlank(message: "A senha é obrigatória.")]
    #[Assert\Length(min: 8, minMessage: "A senha deve ter pelo menos 8 caracteres.")]
    public string $password;

    #[Assert\NotBlank(message: "A confirmação de senha é obrigatória.")]
    #[Assert\Expression(
        "this.password == this.password_confirm",
        message: "As senhas não conferem.",
    )]
    public string $password_confirm;
}
