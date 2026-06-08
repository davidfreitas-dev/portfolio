<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Technology\TechnologyRequestDTO;
use App\Domain\Contract\TechnologyRepositoryInterface;
use App\Domain\Entity\Technology;
use App\Domain\Exception\TechnologyNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TechnologyService
{
    public function __construct(
        private readonly TechnologyRepositoryInterface $repository,
        private readonly ValidatorInterface $validator,
        private readonly FileUploaderService $fileUploader,
    ) {
    }

    public function listTechnologies(int $page, int $limit, string $search): array
    {
        $result = $this->repository->findAll($page, $limit, $search);

        return [
            'technologies' => $result['technologies'],
            'total_items' => $result['total'],
            'current_page' => $page,
            'items_per_page' => $limit,
            'total_pages' => (int)ceil($result['total'] / $limit),
        ];
    }

    public function getTechnology(int $id): Technology
    {
        $technology = $this->repository->findById($id);
        if (!$technology instanceof \App\Domain\Entity\Technology) {
            throw new TechnologyNotFoundException($id);
        }
        return $technology;
    }

    public function createTechnology(TechnologyRequestDTO $dto): Technology
    {
        $dto->validate($this->validator);

        $imageName = null;
        if ($dto->image instanceof \Psr\Http\Message\UploadedFileInterface && $dto->image->getError() === UPLOAD_ERR_OK) {
            $uploadPath = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'technologies';
            $imageName = $this->fileUploader->upload($dto->image, $uploadPath, 'tech');
        }

        $technology = new Technology(
            name: $dto->name,
            slug: $dto->slug,
            image: $imageName,
            sortOrder: $dto->sort_order,
        );

        return $this->repository->save($technology);
    }

    public function updateTechnology(int $id, TechnologyRequestDTO $dto): Technology
    {
        $dto->validate($this->validator);

        $existingTechnology = $this->repository->findById($id);
        if (!$existingTechnology instanceof \App\Domain\Entity\Technology) {
            throw new TechnologyNotFoundException($id);
        }

        $imageName = $existingTechnology->image;
        if ($dto->image instanceof \Psr\Http\Message\UploadedFileInterface && $dto->image->getError() === UPLOAD_ERR_OK) {
            $uploadPath = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'technologies';

            // Delete old image
            if ($imageName) {
                $this->fileUploader->delete($imageName, $uploadPath);
            }

            $imageName = $this->fileUploader->upload($dto->image, $uploadPath, 'tech-' . $id);
        }

        $technology = new Technology(
            name: $dto->name,
            slug: $dto->slug,
            image: $imageName,
            sortOrder: $dto->sort_order,
            id: $id,
            createdAt: $existingTechnology->createdAt,
        );

        return $this->repository->save($technology);
    }

    public function deleteTechnology(int $id): bool
    {
        $existingTechnology = $this->repository->findById($id);
        if (!$existingTechnology instanceof \App\Domain\Entity\Technology) {
            throw new TechnologyNotFoundException($id);
        }

        // Optional: delete image from disk
        if ($existingTechnology->image) {
            $uploadPath = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'technologies';
            $this->fileUploader->delete($existingTechnology->image, $uploadPath);
        }

        return $this->repository->delete($id);
    }
}
