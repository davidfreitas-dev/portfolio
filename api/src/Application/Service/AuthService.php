<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Contract\OtpRepositoryInterface;
use App\Domain\Contract\UserRepositoryInterface;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use App\Shared\Utility\PasswordHelper;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly OtpRepositoryInterface $otpRepository,
        private readonly JwtService $jwtService,
        private readonly MailService $mailService,
    ) {
    }

    public function requestLoginOtp(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            // Silently fail to avoid user enumeration
            return;
        }

        $otp = (string) random_int(100000, 999999);
        $this->otpRepository->create($email, $otp, 600); // 10 minutes

        $this->mailService->sendOtp($user['email'], $user['name'], $otp);
    }

    public function loginWithOtp(string $email, string $otp): array
    {
        if (!$this->otpRepository->verify($email, $otp)) {
            throw new \Exception("Código OTP inválido ou expirado.", HTTPStatus::UNAUTHORIZED);
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception("Usuário não encontrado.", HTTPStatus::UNAUTHORIZED);
        }

        return $this->generateAuthResponse($user);
    }

    public function loginWithPassword(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new \Exception("E-mail ou senha inválidos.", HTTPStatus::UNAUTHORIZED);
        }

        return $this->generateAuthResponse($user);
    }

    public function requestPasswordResetOtp(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return;
        }

        $otp = (string) random_int(100000, 999999);
        $this->otpRepository->create($email, $otp, 600);

        $this->mailService->sendOtp($user['email'], $user['name'], $otp);
    }

    public function validateOtp(string $email, string $code): bool
    {
        if (!$this->otpRepository->verify($email, $code)) {
            throw new \Exception("Código inválido ou expirado.", HTTPStatus::UNAUTHORIZED);
        }

        return true;
    }

    public function resetPassword(string $email, string $code, string $password): bool
    {
        $this->validateOtp($email, $code);

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception("Usuário não encontrado.", HTTPStatus::UNAUTHORIZED);
        }

        PasswordHelper::checkPasswordStrength($password);
        $hashedPassword = PasswordHelper::hashPassword($password);

        return $this->userRepository->updatePassword($user['id'], $hashedPassword);
    }

    private function generateAuthResponse(array $user): array
    {
        $jwt = $this->jwtService->generatePrivateToken($user);

        return [
            "token"      => $jwt,
            "type"       => "Bearer",
            "expires_in" => 3600,
        ];
    }
}
