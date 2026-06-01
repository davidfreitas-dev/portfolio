<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\Model\Technology;

interface TechnologyRepositoryInterface
{
    /**
     * @return array{technologies: Technology[], total: int}
     */
    public function findAll(int $page, int $limit, string $search): array;

    public function findById(int $id): ?Technology;

    public function findBySlug(string $slug): ?Technology;

    public function save(Technology $technology): Technology;

    public function delete(int $id): bool;
}
