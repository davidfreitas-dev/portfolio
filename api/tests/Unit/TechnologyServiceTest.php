<?php

declare(strict_types=1);

use App\Application\DTO\Technology\TechnologyRequestDTO;
use App\Application\Service\FileUploaderService;
use App\Application\Service\TechnologyService;
use App\Domain\Contract\TechnologyRepositoryInterface;
use App\Domain\Entity\Technology;
use App\Domain\Exception\TechnologyNotFoundException;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

beforeEach(function () {
    $this->repository = Mockery::mock(TechnologyRepositoryInterface::class);
    $this->validator = Mockery::mock(ValidatorInterface::class);
    $this->fileUploader = Mockery::mock(FileUploaderService::class);
    $this->service = new TechnologyService($this->repository, $this->validator, $this->fileUploader);
});

afterEach(function () {
    Mockery::close();
});

test('should return list of technologies', function () {
    $page = 1;
    $limit = 10;
    $search = '';
    
    $technologies = [
        new Technology('Tech 1', 'tech-1', null, 1, 1),
        new Technology('Tech 2', 'tech-2', null, 2, 2),
    ];

    $this->repository->shouldReceive('findAll')
        ->with($page, $limit, $search)
        ->once()
        ->andReturn([
            'technologies' => $technologies,
            'total' => 2
        ]);

    $result = $this->service->listTechnologies($page, $limit, $search);

    expect($result)->toBeArray()
        ->and($result['technologies'])->toBe($technologies)
        ->and($result['total_items'])->toBe(2);
});

test('should return a single technology', function () {
    $id = 1;
    $technology = new Technology('Tech 1', 'tech-1', null, 1, $id);

    $this->repository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn($technology);

    $result = $this->service->getTechnology($id);

    expect($result)->toBeInstanceOf(Technology::class)
        ->and($result->id)->toBe($id);
});

test('should throw exception when technology not found', function () {
    $id = 999;

    $this->repository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn(null);

    $this->service->getTechnology($id);
})->throws(TechnologyNotFoundException::class);

test('should create a technology', function () {
    $dto = new TechnologyRequestDTO(
        name: 'New Tech',
        slug: 'new-tech',
        sort_order: 1
    );

    $violations = Mockery::mock(ConstraintViolationListInterface::class);
    $violations->shouldReceive('count')->andReturn(0);
    $this->validator->shouldReceive('validate')->once()->andReturn($violations);
    
    $technology = new Technology('New Tech', 'new-tech', null, 1, 1);
    
    $this->repository->shouldReceive('save')
        ->once()
        ->andReturn($technology);

    $result = $this->service->createTechnology($dto);

    expect($result)->toBeInstanceOf(Technology::class)
        ->and($result->name)->toBe($dto->name);
});

test('should delete a technology', function () {
    $id = 1;
    $technology = new Technology('Tech 1', 'tech-1', null, 1, $id);

    $this->repository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn($technology);

    $this->repository->shouldReceive('delete')
        ->with($id)
        ->once()
        ->andReturn(true);

    $result = $this->service->deleteTechnology($id);

    expect($result)->toBeTrue();
});
