<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\Model\Project;

interface ProjectRepositoryInterface
{
    /**
     * @return array{projects: Project[], total: int}
     */
    public function findAll(int $page, int $limit, string $search): array;

    public function findById(int $id): ?Project;

    public function save(Project $project): Project;

    public function delete(int $id): bool;
}
