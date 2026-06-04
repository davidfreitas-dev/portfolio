<?php

declare(strict_types=1);

use App\Application\Service\ExperienceService;
use App\Domain\Contract\ExperienceRepositoryInterface;
use App\Domain\Entity\Experience;
use App\Domain\Exception\ExperienceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

beforeEach(function () {
    $this->repository = Mockery::mock(ExperienceRepositoryInterface::class);
    $this->validator = Mockery::mock(ValidatorInterface::class);
    $this->service = new ExperienceService($this->repository, $this->validator);
});

afterEach(function () {
    Mockery::close();
});

test('should return list of experiences', function () {
    $page = 1;
    $limit = 10;
    $search = '';
    
    $experiences = [
        new Experience('Title 1', 'Desc 1', new \DateTimeImmutable(), null, 1, 1),
        new Experience('Title 2', 'Desc 2', new \DateTimeImmutable(), null, 2, 2),
    ];

    $this->repository->shouldReceive('findAll')
        ->with($page, $limit, $search)
        ->once()
        ->andReturn([
            'experiences' => $experiences,
            'total' => 2
        ]);

    $result = $this->service->listExperiences($page, $limit, $search);

    expect($result)->toBeArray()
        ->and($result['experiences'])->toBe($experiences)
        ->and($result['total_items'])->toBe(2)
        ->and($result['current_page'])->toBe(1)
        ->and($result['total_pages'])->toBe(1);
});

test('should return a single experience', function () {
    $id = 1;
    $experience = new Experience('Title 1', 'Desc 1', new \DateTimeImmutable(), null, 1, $id);

    $this->repository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn($experience);

    $result = $this->service->getExperience($id);

    expect($result)->toBeInstanceOf(Experience::class)
        ->and($result->id)->toBe($id);
});

test('should throw exception when experience not found', function () {
    $id = 999;

    $this->repository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn(null);

    $this->service->getExperience($id);
})->throws(ExperienceNotFoundException::class);
