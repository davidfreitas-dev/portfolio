<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface OtpRepositoryInterface
{
    public function create(string $email, string $otp, int $ttl): bool;

    public function verify(string $email, string $otp): bool;

    public function check(string $email, string $otp): bool;

    public function invalidate(string $email): bool;
}
