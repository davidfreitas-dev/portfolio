<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Contract\UserRepositoryInterface;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use App\Shared\Utility\PasswordHelper;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function findById(int $id): ?array
    {
        return $this->userRepository->findById($id);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        return $this->userRepository->update($userId, $data);
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->findById($userId);

        // We need the hashed password which findById might not return for security in some contexts,
        // but our UserRepository findByEmail does. Let's check findById in UserRepository.
        // findById does NOT return password. I need to fix that or use findByEmail if I have it.
        // Actually, I'll add a method to get user with password if needed, or just adjust findById.

        // Wait, UserRepository.php findById:
        // SELECT u.id, u.name, u.email, u.phone, u.role_id, r.name as role_name, u.is_active ...
        // No password.

        $userWithPass = $this->userRepository->findByEmail($user['email']);

        if (!password_verify($currentPassword, (string) $userWithPass['password'])) {
            throw new \Exception("A senha atual está incorreta.", HTTPStatus::UNAUTHORIZED);
        }

        PasswordHelper::checkPasswordStrength($newPassword);
        $hashedPassword = PasswordHelper::hashPassword($newPassword);

        return $this->userRepository->updatePassword($userId, $hashedPassword);
    }

    public function deleteAccount(int $userIdToDelete, array $requestingUser): bool
    {
        // 1. Regra de Auto-exclusão:
        // Apenas admins são proibidos de se deletar. Usuários comuns podem.
        if ($userIdToDelete === (int)$requestingUser['id'] && $requestingUser['role_name'] === 'admin') {
            throw new \Exception("Administradores não podem deletar suas próprias contas.", 403);
        }

        $userToDelete = $this->userRepository->findById($userIdToDelete);
        
        if (!$userToDelete) {
            throw new \Exception("Usuário não encontrado.", 404);
        }

        // 2. Regra: Admin só pode ser deletado por outro Admin
        if ($userToDelete['role_name'] === 'admin' && $requestingUser['role_name'] !== 'admin') {
            throw new \Exception("Apenas administradores podem deletar outros administradores.", 403);
        }

        return $this->userRepository->delete($userIdToDelete);
    }
}
