<?php

declare(strict_types=1);

use App\Application\Service\AuthService;
use App\Application\Service\JwtService;
use App\Application\Service\MailService;
use App\Domain\Contract\OtpRepositoryInterface;
use App\Domain\Contract\UserRepositoryInterface;

beforeEach(function () {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->otpRepository = Mockery::mock(OtpRepositoryInterface::class);
    $this->jwtService = Mockery::mock(JwtService::class);
    $this->mailService = Mockery::mock(MailService::class);
    $this->service = new AuthService(
        $this->userRepository,
        $this->otpRepository,
        $this->jwtService,
        $this->mailService
    );
});

afterEach(function () {
    Mockery::close();
});

test('should request login otp', function () {
    $email = 'user@test.com';
    $user = ['id' => 1, 'name' => 'Test User', 'email' => $email];

    $this->userRepository->shouldReceive('findByEmail')
        ->with($email)
        ->once()
        ->andReturn($user);

    $this->otpRepository->shouldReceive('create')
        ->once()
        ->with($email, Mockery::type('string'), 600)
        ->andReturn(true);

    $this->mailService->shouldReceive('sendOtp')
        ->once()
        ->with($email, $user['name'], Mockery::type('string'));

    $this->service->requestLoginOtp($email);
});

test('should login with password', function () {
    $email = 'user@test.com';
    $password = 'Password123!';
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $user = ['id' => 1, 'name' => 'Test User', 'email' => $email, 'password' => $hashedPassword, 'role_name' => 'user'];

    $this->userRepository->shouldReceive('findByEmail')
        ->with($email)
        ->once()
        ->andReturn($user);

    $this->jwtService->shouldReceive('generatePrivateToken')
        ->with($user)
        ->once()
        ->andReturn('fake-jwt-token');

    $this->jwtService->shouldReceive('generateRefreshToken')
        ->with($user)
        ->once()
        ->andReturn('fake-refresh-token');

    $result = $this->service->loginWithPassword($email, $password);

    expect($result)->toBeArray()
        ->and($result['token'])->toBe('fake-jwt-token')
        ->and($result['refresh_token'])->toBe('fake-refresh-token')
        ->and($result['type'])->toBe('Bearer');
});

test('should fail login with wrong password', function () {
    $email = 'user@test.com';
    $password = 'WrongPassword';
    $hashedPassword = password_hash('CorrectPassword123!', PASSWORD_BCRYPT);
    $user = ['id' => 1, 'email' => $email, 'password' => $hashedPassword];

    $this->userRepository->shouldReceive('findByEmail')
        ->with($email)
        ->once()
        ->andReturn($user);

    $this->service->loginWithPassword($email, $password);
})->throws(Exception::class, 'E-mail ou senha inválidos.');

test('should login with otp', function () {
    $email = 'user@test.com';
    $otp = '123456';
    $user = ['id' => 1, 'name' => 'Test User', 'email' => $email, 'role_name' => 'user'];

    $this->otpRepository->shouldReceive('verify')
        ->with($email, $otp)
        ->once()
        ->andReturn(true);

    $this->userRepository->shouldReceive('findByEmail')
        ->with($email)
        ->once()
        ->andReturn($user);

    $this->jwtService->shouldReceive('generatePrivateToken')
        ->with($user)
        ->once()
        ->andReturn('fake-jwt-token');

    $this->jwtService->shouldReceive('generateRefreshToken')
        ->with($user)
        ->once()
        ->andReturn('fake-refresh-token');

    $result = $this->service->loginWithOtp($email, $otp);

    expect($result['token'])->toBe('fake-jwt-token');
});
