<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Service\ErrorLoggerService;
use App\Domain\Entity\ErrorLog;
use App\Domain\Contract\ErrorLogRepositoryInterface;

beforeEach(function () {
    $this->repository = \Mockery::mock(ErrorLogRepositoryInterface::class);
    $this->service = new ErrorLoggerService($this->repository);
});

afterEach(function () {
    \Mockery::close();
});

test('should log an error', function () {
    $severity = 'CRITICAL';
    $message = 'Something went wrong';
    $context = ['stack_trace' => '...'];

    $this->repository->shouldReceive('save')
        ->once()
        ->with(\Mockery::on(function (ErrorLog $log) use ($severity, $message, $context) {
            return $log->severity === $severity &&
                   $log->message === $message &&
                   $log->context === $context;
        }))
        ->andReturn(new ErrorLog($severity, $message, $context));

    $result = $this->service->log($severity, $message, $context);

    expect($result)->toBeInstanceOf(ErrorLog::class)
        ->and($result->severity)->toBe($severity)
        ->and($result->message)->toBe($message);
});

test('should log a resolved error', function () {
    $severity = 'ERROR';
    $message = 'Resolved immediately';
    $userId = 1;

    $this->repository->shouldReceive('save')
        ->once()
        ->with(\Mockery::on(function (ErrorLog $log) use ($userId) {
            return $log->resolvedBy === $userId && $log->resolvedAt !== null;
        }))
        ->andReturn(new ErrorLog($severity, $message, resolvedBy: $userId));

    $result = $this->service->log($severity, $message, resolvedBy: $userId);

    expect($result->resolvedBy)->toBe($userId);
});

test('should mark as resolved', function () {
    $id = 1;
    $userId = 2;

    $this->repository->shouldReceive('markAsResolved')
        ->once()
        ->with($id, $userId)
        ->andReturn(true);

    $result = $this->service->markAsResolved($id, $userId);

    expect($result)->toBeTrue();
});
