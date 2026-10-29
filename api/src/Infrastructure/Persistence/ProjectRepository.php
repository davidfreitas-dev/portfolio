<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Contract\ProjectRepositoryInterface;
use App\Domain\Entity\Project;
use DateTimeImmutable;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findAll(int $page, int $limit, string $search, ?bool $isActive = null): array
    {
        $params = [];
        $where = ["deleted_at IS NULL"];
        $search = trim($search);

        if ($search !== '' && $search !== '0') {
            $where[] = "(title LIKE :search OR summary LIKE :search OR description LIKE :search)";
            $params[":search"] = "%$search%";
        }

        if ($isActive !== null) {
            $where[] = "is_active = :is_active";
            $params[":is_active"] = $isActive ? 1 : 0;
        }

        $whereClause = implode(" AND ", $where);
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM projects 
                WHERE $whereClause 
                ORDER BY sort_order ASC, created_at DESC 
                LIMIT $offset, $limit";

        $results = $this->db->select($sql, $params);
        $total = $this->db->select("SELECT COUNT(*) as total FROM projects WHERE $whereClause", $params)[0]['total'];

        $projects = array_map($this->mapRowToProject(...), $results);

        return ['projects' => $projects, 'total' => (int)$total];
    }

    public function findById(int $id): ?Project
    {
        $sql = "SELECT * FROM projects WHERE id = :id AND deleted_at IS NULL";
        $results = $this->db->select($sql, [':id' => $id]);

        if ($results === []) {
            return null;
        }

        return $this->mapRowToProject($results[0]);
    }

    public function save(Project $project): Project
    {
        $technologiesJson = !empty($project->technologies)
            ? json_encode(array_values($project->technologies), JSON_UNESCAPED_UNICODE)
            : null;

        $params = [
            ':title' => $project->title,
            ':slug' => $project->slug,
            ':summary' => $project->summary,
            ':description' => $project->description,
            ':image' => $project->image,
            ':link' => $project->link,
            ':github_link' => $project->githubLink,
            ':sort_order' => $project->sortOrder,
            ':is_active' => $project->isActive ? 1 : 0,
            ':technologies' => $technologiesJson,
        ];

        if ($project->id) {
            $params[':id'] = $project->id;
            $sql = "UPDATE projects 
                    SET title = :title, slug = :slug, summary = :summary, description = :description, 
                        image = :image, link = :link, github_link = :github_link, 
                        sort_order = :sort_order, is_active = :is_active, technologies = :technologies 
                    WHERE id = :id";
            $this->db->query($sql, $params);
            return $this->findById($project->id);
        }

        $sql = "INSERT INTO projects (title, slug, summary, description, image, link, github_link, sort_order, is_active, technologies) 
                VALUES (:title, :slug, :summary, :description, :image, :link, :github_link, :sort_order, :is_active, :technologies)";
        $id = $this->db->insert($sql, $params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE projects SET deleted_at = NOW() WHERE id = :id";
        $this->db->query($sql, [':id' => $id]);
        return true;
    }

    private function mapRowToProject(array $row): Project
    {
        $technologies = [];
        if (!empty($row['technologies'])) {
            $decoded = is_string($row['technologies']) ? json_decode($row['technologies'], true) : $row['technologies'];
            if (is_array($decoded)) {
                $technologies = $decoded;
            }
        }

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
            technologies: $technologies,
            id: (int)$row['id'],
            createdAt: new DateTimeImmutable($row['created_at']),
            updatedAt: new DateTimeImmutable($row['updated_at']),
        );
    }
}
