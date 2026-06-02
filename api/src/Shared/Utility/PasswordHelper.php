<?php

declare(strict_types=1);

namespace App\Shared\Utility;

use App\Shared\Enum\HttpStatus as HTTPStatus;

class PasswordHelper
{
    public static function checkPasswordStrength($password): void
    {

        if (strlen((string) $password) < 8) {

            throw new \Exception("A senha deve conter pelo menos 8 caracteres.", HTTPStatus::BAD_REQUEST);

        }

        if (!preg_match('/[a-z]/', (string) $password) || !preg_match('/[A-Z]/', (string) $password)) {

            throw new \Exception("A senha deve conter letras maiúsculas e minúsculas.", HTTPStatus::BAD_REQUEST);

        }

        if (!preg_match('/\d/', (string) $password)) {

            throw new \Exception("A senha deve conter pelo menos um número.", HTTPStatus::BAD_REQUEST);

        }

        if (!preg_match('/[\W_]/', (string) $password)) {

            throw new \Exception("A senha deve conter pelo menos um caractere especial.", HTTPStatus::BAD_REQUEST);

        }

    }

    public static function hashPassword($password): string
    {

        return password_hash((string) $password, PASSWORD_BCRYPT, [
            'cost' => 12,
        ]);

    }
}
