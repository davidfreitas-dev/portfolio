<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Contract\ProjectRepositoryInterface;
use App\Domain\Model\Project;
use DateTimeImmutable;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findAll(int $page, int $limit, string $search): array
    {
        $params = [];
        $where = ["deleted_at IS NULL"];
        $search = trim($search);

        if (!empty($search)) {
            $where[] = "(title LIKE :search OR summary LIKE :search OR description LIKE :search)";
            $params[":search"] = "%$search%";
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM projects WHERE " . implode(" AND ", $where) . " ORDER BY sort_order ASC, created_at DESC LIMIT " . (($page - 1) * $limit) . ", $limit";

        $results = $this->db->select($sql, $params);
        $total = $this->db->select("SELECT FOUND_ROWS() as total")[0]['total'];

        $projects = array_map(fn ($row) => $this->mapRowToProject($row), $results);

        return ['projects' => $projects, 'total' => (int)$total];
    }

    public function findById(int $id): ?Project
    {
        $sql = "SELECT * FROM projects WHERE id = :id AND deleted_at IS NULL";
        $results = $this->db->select($sql, [':id' => $id]);

        return $results ? $this->mapRowToProject($results[0]) : null;
    }

    public function save(Project $project): Project
    {
        // For now, simple implementation
        return $project;
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE projects SET deleted_at = NOW() WHERE id = :id";
        $this->db->query($sql, [':id' => $id]);
        return true;
    }

    private function mapRowToProject(array $row): Project
    {
        return new Project(
            title: $row['title'],
            description: $row['description'],
            slug: $row['slug'],
            summary: $row['summary'],
            image: $row['image'],
            link: $row['link'],
            githubLink: $row['github_link'],
            sortOrder: (int)$row['sort_order'],
            isActive: (bool)$row['is_active'],
            id: (int)$row['id'],
            createdAt: new DateTimeImmutable($row['created_at']),
            updatedAt: new DateTimeImmutable($row['updated_at']),
        );
    }
}
