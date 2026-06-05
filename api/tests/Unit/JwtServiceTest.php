<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Service\JwtService;

beforeEach(function () {
    $this->secret = 'test_secret_key_12345678901234567890123456789012';
    $this->service = new JwtService($this->secret);
});

test('should create and decode a token', function () {
    $userData = ['id' => 1, 'role_name' => 'admin'];
    $token = $this->service->createToken($userData);

    expect($token)->toBeString();

    $decoded = $this->service->decodeToken($token);

    expect($decoded)->toBeArray()
        ->and($decoded['user'])->toBe($userData)
        ->and($decoded['sub'])->toBe('user-client')
        ->and($decoded['iss'])->toBe('portfolio-api');
});

test('should generate public token', function () {
    $token = $this->service->generatePublicToken();
    $decoded = $this->service->decodeToken($token);

    expect($decoded['user']['role_name'])->toBe('public')
        ->and($decoded['sub'])->toBe('site-client');
});

test('should generate private token', function () {
    $data = ['id' => 123, 'role_name' => 'editor'];
    $token = $this->service->generatePrivateToken($data);
    $decoded = $this->service->decodeToken($token);

    expect($decoded['user']['id'])->toBe(123)
        ->and($decoded['user']['role_name'])->toBe('editor')
        ->and($decoded['sub'])->toBe('user-client');
});

test('should throw exception for invalid token', function () {
    $this->service->decodeToken('invalid.token.here');
})->throws(\Exception::class);
