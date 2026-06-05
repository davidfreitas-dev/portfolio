<?php

declare(strict_types=1);

use App\Application\Service\UserService;
use App\Domain\Contract\UserRepositoryInterface;

beforeEach(function () {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->service = new UserService($this->userRepository);
});

afterEach(function () {
    Mockery::close();
});

test('should find user by id', function () {
    $id = 1;
    $user = ['id' => $id, 'name' => 'Test User', 'email' => 'test@user.com'];

    $this->userRepository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn($user);

    $result = $this->service->findById($id);

    expect($result)->toBe($user);
});

test('should update profile', function () {
    $id = 1;
    $data = ['name' => 'Updated Name'];

    $this->userRepository->shouldReceive('update')
        ->with($id, $data)
        ->once()
        ->andReturn(true);

    $result = $this->service->updateProfile($id, $data);

    expect($result)->toBeTrue();
});

test('should delete account if not admin', function () {
    $userId = 2;
    $requestingUser = ['id' => 2, 'role_name' => 'user'];
    $userToDelete = ['id' => 2, 'role_name' => 'user'];

    $this->userRepository->shouldReceive('findById')
        ->with($userId)
        ->once()
        ->andReturn($userToDelete);

    $this->userRepository->shouldReceive('delete')
        ->with($userId)
        ->once()
        ->andReturn(true);

    $result = $this->service->deleteAccount($userId, $requestingUser);

    expect($result)->toBeTrue();
});

test('should prevent admin from deleting self', function () {
    $userId = 1;
    $requestingUser = ['id' => 1, 'role_name' => 'admin'];

    $this->service->deleteAccount($userId, $requestingUser);
})->throws(Exception::class, 'Administradores não podem deletar suas próprias contas.');
