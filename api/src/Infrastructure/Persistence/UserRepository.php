<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Contract\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT u.id, u.name, u.email, u.phone, u.role_id, r.name as role_name, u.is_active
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id AND u.deleted_at IS NULL";

        $results = $this->db->select($sql, [':id' => $id]);

        return $results[0] ?? null;
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT u.id, u.name, u.email, u.password, u.role_id, r.name as role_name, u.is_active
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email AND u.deleted_at IS NULL";

        $results = $this->db->select($sql, [':email' => strtolower(trim($email))]);

        return $results[0] ?? null;
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $sql = "UPDATE users SET password = :password WHERE id = :id";

        $this->db->query($sql, [
            ':password' => $hashedPassword,
            ':id' => $userId,
        ]);

        return true;
    }

    public function update(int $userId, array $data): bool
    {
        $fields = [];
        $params = [':id' => $userId];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";

        $this->db->query($sql, $params);

        return true;
    }

    public function delete(int $userId): bool
    {
        $sql = "UPDATE users SET deleted_at = NOW() WHERE id = :id";

        $this->db->query($sql, [':id' => $userId]);

        return true;
    }
}
