<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Experience\ExperienceRequestDTO;
use App\Domain\Contract\ExperienceRepositoryInterface;
use App\Domain\Entity\Experience;
use App\Domain\Exception\ExperienceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExperienceService
{
    public function __construct(
        private readonly ExperienceRepositoryInterface $repository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function listExperiences(int $page, int $limit, string $search): array
    {
        $result = $this->repository->findAll($page, $limit, $search);

        return [
            'experiences' => $result['experiences'],
            'total_items' => $result['total'],
            'current_page' => $page,
            'items_per_page' => $limit,
            'total_pages' => (int)ceil($result['total'] / $limit),
        ];
    }

    public function getExperience(int $id): Experience
    {
        $experience = $this->repository->findById($id);
        if (!$experience instanceof \App\Domain\Entity\Experience) {
            throw new ExperienceNotFoundException($id);
        }
        return $experience;
    }

    public function createExperience(ExperienceRequestDTO $dto): Experience
    {
        $dto->validate($this->validator);

        $experience = new Experience(
            title: $dto->title,
            description: $dto->description,
            startDate: new \DateTimeImmutable($dto->start_date),
            endDate: $dto->end_date ? new \DateTimeImmutable($dto->end_date) : null,
            sortOrder: $dto->sort_order,
        );

        return $this->repository->save($experience);
    }

    public function updateExperience(int $id, ExperienceRequestDTO $dto): Experience
    {
        $dto->validate($this->validator);

        $existingExperience = $this->repository->findById($id);
        if (!$existingExperience instanceof \App\Domain\Entity\Experience) {
            throw new ExperienceNotFoundException($id);
        }

        $experience = new Experience(
            title: $dto->title,
            description: $dto->description,
            startDate: new \DateTimeImmutable($dto->start_date),
            endDate: $dto->end_date ? new \DateTimeImmutable($dto->end_date) : null,
            sortOrder: $dto->sort_order,
            id: $id,
            createdAt: $existingExperience->createdAt,
        );

        return $this->repository->save($experience);
    }

    public function deleteExperience(int $id): bool
    {
        $existingExperience = $this->repository->findById($id);
        if (!$existingExperience instanceof \App\Domain\Entity\Experience) {
            throw new ExperienceNotFoundException($id);
        }

        return $this->repository->delete($id);
    }
}
