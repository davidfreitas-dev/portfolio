<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Contract\TechnologyRepositoryInterface;
use App\Domain\Model\Technology;
use DateTimeImmutable;

class TechnologyRepository implements TechnologyRepositoryInterface
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
            $where[] = "(name LIKE :search OR slug LIKE :search)";
            $params[":search"] = "%$search%";
        }

        $offset = ($page - 1) * $limit;
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM technologies 
                WHERE " . implode(" AND ", $where) . " 
                ORDER BY sort_order ASC, name ASC 
                LIMIT $offset, $limit";

        $results = $this->db->select($sql, $params);
        $total = $this->db->select("SELECT FOUND_ROWS() as total")[0]['total'];

        $technologies = array_map(fn ($row) => $this->mapRowToTechnology($row), $results);

        return ['technologies' => $technologies, 'total' => (int)$total];
    }

    public function findById(int $id): ?Technology
    {
        $sql = "SELECT * FROM technologies WHERE id = :id AND deleted_at IS NULL";
        $results = $this->db->select($sql, [':id' => $id]);

        return $results ? $this->mapRowToTechnology($results[0]) : null;
    }

    public function findBySlug(string $slug): ?Technology
    {
        $sql = "SELECT * FROM technologies WHERE slug = :slug AND deleted_at IS NULL";
        $results = $this->db->select($sql, [':slug' => $slug]);

        return $results ? $this->mapRowToTechnology($results[0]) : null;
    }

    public function save(Technology $technology): Technology
    {
        $params = [
            ':name' => $technology->name,
            ':slug' => $technology->slug,
            ':image' => $technology->image,
            ':sort_order' => $technology->sortOrder,
        ];

        if ($technology->id) {
            $params[':id'] = $technology->id;
            $sql = "UPDATE technologies 
                    SET name = :name, slug = :slug, image = :image, sort_order = :sort_order 
                    WHERE id = :id";
            $this->db->query($sql, $params);
            return $this->findById($technology->id);
        }

        $sql = "INSERT INTO technologies (name, slug, image, sort_order) 
                VALUES (:name, :slug, :image, :sort_order)";
        $id = $this->db->insert($sql, $params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE technologies SET deleted_at = NOW() WHERE id = :id";
        $this->db->query($sql, [':id' => $id]);
        return true;
    }

    private function mapRowToTechnology(array $row): Technology
    {
        return new Technology(
            name: $row['name'],
            slug: $row['slug'],
            image: $row['image'],
            sortOrder: (int)$row['sort_order'],
            id: (int)$row['id'],
            createdAt: new DateTimeImmutable($row['created_at']),
            updatedAt: new DateTimeImmutable($row['updated_at']),
        );
    }
}
