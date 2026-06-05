<?php

declare(strict_types=1);

use App\Application\DTO\Project\ProjectRequestDTO;
use App\Application\Service\FileUploaderService;
use App\Application\Service\ProjectService;
use App\Domain\Contract\ProjectRepositoryInterface;
use App\Domain\Entity\Project;
use App\Domain\Exception\ProjectNotFoundException;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

beforeEach(function () {
    $this->repository = Mockery::mock(ProjectRepositoryInterface::class);
    $this->validator = Mockery::mock(ValidatorInterface::class);
    $this->fileUploader = Mockery::mock(FileUploaderService::class);
    $this->service = new ProjectService($this->repository, $this->validator, $this->fileUploader);
});

afterEach(function () {
    Mockery::close();
});

test('should return list of projects', function () {
    $page = 1;
    $limit = 10;
    $search = '';
    
    $projects = [
        new Project('Project 1', 'Desc 1', 'project-1', 'Summary 1', null, null, null, 1, true, [], 1),
        new Project('Project 2', 'Desc 2', 'project-2', 'Summary 2', null, null, null, 2, true, [], 2),
    ];

    $this->repository->shouldReceive('findAll')
        ->with($page, $limit, $search)
        ->once()
        ->andReturn([
            'projects' => $projects,
            'total' => 2
        ]);

    $result = $this->service->listProjects($page, $limit, $search);

    expect($result)->toBeArray()
        ->and($result['projects'])->toBe($projects)
        ->and($result['total_items'])->toBe(2)
        ->and($result['current_page'])->toBe(1)
        ->and($result['total_pages'])->toBe(1);
});

test('should return a single project', function () {
    $id = 1;
    $project = new Project('Project 1', 'Desc 1', 'project-1', 'Summary 1', null, null, null, 1, true, [], $id);

    $this->repository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn($project);

    $result = $this->service->getProject($id);

    expect($result)->toBeInstanceOf(Project::class)
        ->and($result->id)->toBe($id);
});

test('should throw exception when project not found', function () {
    $id = 999;

    $this->repository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn(null);

    $this->service->getProject($id);
})->throws(ProjectNotFoundException::class);

test('should create a project without image', function () {
    $dto = new ProjectRequestDTO(
        title: 'New Project',
        description: 'New Desc',
        slug: 'new-project',
        summary: 'New Summary',
        sort_order: 1,
        is_active: true,
        technology_ids: [1, 2]
    );

    $violations = Mockery::mock(ConstraintViolationListInterface::class);
    $violations->shouldReceive('count')->andReturn(0);
    $this->validator->shouldReceive('validate')->once()->andReturn($violations);
    
    $project = new Project('New Project', 'New Desc', 'new-project', 'New Summary', null, null, null, 1, true, [1, 2]);
    
    $this->repository->shouldReceive('save')
        ->once()
        ->with(Mockery::on(function ($p) use ($dto) {
            return $p->title === $dto->title && $p->image === null;
        }))
        ->andReturn($project);

    $result = $this->service->createProject($dto);

    expect($result)->toBeInstanceOf(Project::class)
        ->and($result->title)->toBe($dto->title);
});

test('should create a project with image', function () {
    $image = Mockery::mock(UploadedFileInterface::class);
    $dto = new ProjectRequestDTO(
        title: 'New Project',
        description: 'New Desc',
        image: $image
    );

    $violations = Mockery::mock(ConstraintViolationListInterface::class);
    $violations->shouldReceive('count')->andReturn(0);
    $this->validator->shouldReceive('validate')->once()->andReturn($violations);
    
    $this->fileUploader->shouldReceive('upload')
        ->once()
        ->andReturn('uploaded_image.png');

    $this->repository->shouldReceive('save')
        ->once()
        ->with(Mockery::on(function ($p) {
            return $p->image === 'uploaded_image.png';
        }))
        ->andReturn(new Project('New Project', 'New Desc', image: 'uploaded_image.png'));

    $result = $this->service->createProject($dto);

    expect($result->image)->toBe('uploaded_image.png');
});

test('should delete a project and its image', function () {
    $id = 1;
    $project = new Project('Project 1', 'Desc 1', image: 'old_image.png', id: $id);

    $this->repository->shouldReceive('findById')
        ->with($id)
        ->once()
        ->andReturn($project);

    $this->fileUploader->shouldReceive('delete')
        ->once()
        ->with('old_image.png', Mockery::any());

    $this->repository->shouldReceive('delete')
        ->with($id)
        ->once()
        ->andReturn(true);

    $result = $this->service->deleteProject($id);

    expect($result)->toBeTrue();
});
