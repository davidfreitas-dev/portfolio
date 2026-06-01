<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface UserRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByEmail(string $email): ?array;

    public function update(int $userId, array $data): bool;

    public function updatePassword(int $userId, string $hashedPassword): bool;

    public function delete(int $userId): bool;
}
