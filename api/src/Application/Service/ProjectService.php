<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Project\ProjectRequestDTO;
use App\Domain\Contract\ProjectRepositoryInterface;
use App\Domain\Entity\Project;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private ValidatorInterface $validator,
        private FileUploaderService $fileUploader,
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

        $imageName = null;
        if ($dto->image) {
            $uploadPath = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'projects';
            $imageName = $this->fileUploader->upload($dto->image, $uploadPath, 'project');
        }

        $project = new Project(
            title: $dto->title,
            description: $dto->description,
            slug: $dto->slug,
            summary: $dto->summary,
            image: $imageName,
            link: $dto->link,
            githubLink: $dto->github_link,
            sortOrder: $dto->sort_order,
            isActive: $dto->is_active,
            technologies: $dto->technology_ids,
        );

        return $this->repository->save($project);
    }

    public function updateProject(int $id, ProjectRequestDTO $dto): Project
    {
        $dto->validate($this->validator);

        $existingProject = $this->repository->findById($id);
        if (!$existingProject) {
            throw new \App\Domain\Exception\ProjectNotFoundException($id);
        }

        $imageName = $existingProject->image;
        if ($dto->image) {
            $uploadPath = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'projects';

            // Delete old image
            if ($imageName) {
                $this->fileUploader->delete($imageName, $uploadPath);
            }

            $imageName = $this->fileUploader->upload($dto->image, $uploadPath, 'project-' . $id);
        }

        $project = new Project(
            title: $dto->title,
            description: $dto->description,
            slug: $dto->slug ?? $existingProject->slug,
            summary: $dto->summary ?? $existingProject->summary,
            image: $imageName,
            link: $dto->link ?? $existingProject->link,
            githubLink: $dto->github_link ?? $existingProject->githubLink,
            sortOrder: $dto->sort_order,
            isActive: $dto->is_active,
            technologies: $dto->technology_ids,
            id: $id,
            createdAt: $existingProject->createdAt,
        );

        return $this->repository->save($project);
    }

    public function deleteProject(int $id): bool
    {
        $existingProject = $this->repository->findById($id);
        if (!$existingProject) {
            throw new \App\Domain\Exception\ProjectNotFoundException($id);
        }

        // Delete image from disk
        if ($existingProject->image) {
            $uploadPath = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'projects';
            $this->fileUploader->delete($existingProject->image, $uploadPath);
        }

        return $this->repository->delete($id);
    }
}
