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
        private readonly ProjectRepositoryInterface $repository,
        private readonly ValidatorInterface $validator,
        private readonly FileUploaderService $fileUploader,
    ) {
    }

    public function listProjects(int $page, int $limit, string $search, ?bool $isActive = null): array
    {
        $result = $this->repository->findAll($page, $limit, $search, $isActive);

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
        if (!$project instanceof \App\Domain\Entity\Project) {
            throw new \App\Domain\Exception\ProjectNotFoundException($id);
        }
        return $project;
    }

    public function createProject(ProjectRequestDTO $dto): Project
    {
        $dto->validate($this->validator);

        $imageName = null;
        $uploadPath = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'projects';
        if ($dto->image instanceof \Psr\Http\Message\UploadedFileInterface && $dto->image->getError() === UPLOAD_ERR_OK) {
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
            technologies: $dto->technologies,
        );

        try {
            return $this->repository->save($project);
        } catch (\Throwable $e) {
            if ($imageName) {
                $this->fileUploader->delete($imageName, $uploadPath);
            }
            throw $e;
        }
    }

    public function updateProject(int $id, ProjectRequestDTO $dto): Project
    {
        $dto->validate($this->validator);

        $existingProject = $this->repository->findById($id);
        if (!$existingProject instanceof \App\Domain\Entity\Project) {
            throw new \App\Domain\Exception\ProjectNotFoundException($id);
        }

        $imageName = $existingProject->image;
        $uploadPath = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'projects';
        $oldImageToDelete = null;

        $hasNewImage = $dto->image instanceof \Psr\Http\Message\UploadedFileInterface && $dto->image->getError() === UPLOAD_ERR_OK;

        if ($dto->remove_image && !$hasNewImage) {
            if ($imageName) {
                $oldImageToDelete = $imageName;
                $imageName = null;
            }
        } elseif ($hasNewImage) {
            if ($imageName) {
                $oldImageToDelete = $imageName;
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
            technologies: $dto->technologies,
            id: $id,
            createdAt: $existingProject->createdAt,
        );

        $savedProject = $this->repository->save($project);

        if ($oldImageToDelete !== null) {
            $this->fileUploader->delete($oldImageToDelete, $uploadPath);
        }

        return $savedProject;
    }

    public function deleteProject(int $id): bool
    {
        $existingProject = $this->repository->findById($id);
        if (!$existingProject instanceof \App\Domain\Entity\Project) {
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
