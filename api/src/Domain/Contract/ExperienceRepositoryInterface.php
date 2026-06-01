<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\Model\Experience;

interface ExperienceRepositoryInterface
{
    /**
     * @return array{experiences: Experience[], total: int}
     */
    public function findAll(int $page, int $limit, string $search): array;

    public function findById(int $id): ?Experience;

    public function save(Experience $experience): Experience;

    public function delete(int $id): bool;
}
