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

    public function findAll(int $page, int $limit, string $search, bool $onlyActive = false): array
    {
        $params = [];
        $where = ["deleted_at IS NULL"];
        $search = trim($search);

        if ($search !== '' && $search !== '0') {
            $where[] = "(title LIKE :search OR summary LIKE :search OR description LIKE :search)";
            $params[":search"] = "%$search%";
        }

        if ($onlyActive) {
            $where[] = "is_active = 1";
        }

        $whereClause = implode(" AND ", $where);
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM projects 
                WHERE $whereClause 
                ORDER BY sort_order ASC, created_at DESC 
                LIMIT $offset, $limit";

        $results = $this->db->select($sql, $params);
        $total = $this->db->select("SELECT COUNT(*) as total FROM projects WHERE $whereClause", $params)[0]['total'];

        $projects = [];
        foreach ($results as $row) {
            $project = $this->mapRowToProject($row);
            $projects[] = $this->loadTechnologies($project);
        }

        return ['projects' => $projects, 'total' => (int)$total];
    }

    public function findById(int $id): ?Project
    {
        $sql = "SELECT * FROM projects WHERE id = :id AND deleted_at IS NULL";
        $results = $this->db->select($sql, [':id' => $id]);

        if ($results === []) {
            return null;
        }

        $project = $this->mapRowToProject($results[0]);
        return $this->loadTechnologies($project);
    }

    public function save(Project $project): Project
    {
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
        ];

        if ($project->id) {
            $params[':id'] = $project->id;
            $sql = "UPDATE projects 
                    SET title = :title, slug = :slug, summary = :summary, description = :description, 
                        image = :image, link = :link, github_link = :github_link, 
                        sort_order = :sort_order, is_active = :is_active 
                    WHERE id = :id";
            $this->db->query($sql, $params);
            $this->syncTechnologies($project->id, $project->technologies);
            return $this->findById($project->id);
        }

        $sql = "INSERT INTO projects (title, slug, summary, description, image, link, github_link, sort_order, is_active) 
                VALUES (:title, :slug, :summary, :description, :image, :link, :github_link, :sort_order, :is_active)";
        $id = $this->db->insert($sql, $params);
        $this->syncTechnologies($id, $project->technologies);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE projects SET deleted_at = NOW() WHERE id = :id";
        $this->db->query($sql, [':id' => $id]);
        return true;
    }

    private function syncTechnologies(int $projectId, array $technologyIds): void
    {
        // Remove existing associations
        $this->db->query("DELETE FROM project_technologies WHERE project_id = :project_id", [':project_id' => $projectId]);

        // Add new associations
        foreach ($technologyIds as $techId) {
            $this->db->query(
                "INSERT INTO project_technologies (project_id, technology_id) VALUES (:project_id, :technology_id)",
                [':project_id' => $projectId, ':technology_id' => (int)$techId],
            );
        }
    }

    private function loadTechnologies(Project $project): Project
    {
        $sql = "SELECT t.* FROM technologies t 
                JOIN project_technologies pt ON t.id = pt.technology_id 
                WHERE pt.project_id = :project_id AND t.deleted_at IS NULL";

        $results = $this->db->select($sql, [':project_id' => $project->id]);

        // Map technologies to array (could use a Technology model if needed, but for now array/DTO style)
        $technologies = array_map(fn (array $row): array => [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
                'image' => $row['image'],
            ], $results);

        // We need to use reflection or a setter if the property is private(set)
        // But Project model has technologies as public private(set) array $technologies;
        // In PHP 8.4 property hooks, private(set) means it can only be set from within the class.
        // Wait, Project.php uses: public private(set) array $technologies;
        // This is PHP 8.4 syntax. I should check if I can set it here.
        // If it's private(set), I can't set it from Repository unless I use a constructor or reflection.

        // Let's re-read Project.php

        return new Project(
            title: $project->title,
            description: $project->description,
            slug: $project->slug,
            summary: $project->summary,
            image: $project->image,
            link: $project->link,
            githubLink: $project->githubLink,
            sortOrder: $project->sortOrder,
            isActive: $project->isActive,
            technologies: $technologies,
            id: $project->id,
            createdAt: $project->createdAt,
            updatedAt: $project->updatedAt,
        );
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
