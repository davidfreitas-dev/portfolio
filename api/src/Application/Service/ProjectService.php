<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Project\ProjectRequestDTO;
use App\Domain\Contract\ProjectRepositoryInterface;
use App\Domain\Model\Project;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private ValidatorInterface $validator,
    ) {
    }

    public function listProjects(int $page, int $limit, string $search): array
    {
        $result = $this->repository->findAll($page, $limit, $search);

        return [
            'projects' => $result['projects'],
            'total_items' => $result['total'],
            'current_page' => $page,
            'items_per_page' => $limit,
            'total_pages' => (int)ceil($result['total'] / $limit),
        ];
    }

    public function getProject(int $id): Project
    {
        $project = $this->repository->findById($id);
        if (!$project) {
            throw new \App\Domain\Exception\ProjectNotFoundException($id);
        }
        return $project;
    }

    public function createProject(ProjectRequestDTO $dto): Project
    {
        $dto->validate($this->validator);

        $project = new Project(
            title: $dto->title,
            description: $dto->description,
            slug: $dto->slug,
            summary: $dto->summary,
            image: null, // Image handling would be here or in a specialized service
            link: $dto->link,
            githubLink: $dto->github_link,
            sortOrder: $dto->sort_order,
            isActive: $dto->is_active,
        );

        return $this->repository->save($project);
    }

    public function updateProject(int $id, ProjectRequestDTO $dto): Project
    {
        $dto->validate($this->validator);

        $project = $this->repository->findById($id);
        if (!$project) {
            throw new \App\Domain\Exception\ProjectNotFoundException($id);
        }

        // Logic to update the entity would go here

        return $this->repository->save($project);
    }

    public function deleteProject(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
