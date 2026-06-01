<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Contract\ExperienceRepositoryInterface;
use App\Domain\Model\Experience;
use DateTimeImmutable;

class ExperienceRepository implements ExperienceRepositoryInterface
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
            $where[] = "(title LIKE :search OR description LIKE :search)";
            $params[":search"] = "%$search%";
        }

        $offset = ($page - 1) * $limit;
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM experiences 
                WHERE " . implode(" AND ", $where) . " 
                ORDER BY sort_order ASC, start_date DESC 
                LIMIT $offset, $limit";

        $results = $this->db->select($sql, $params);
        $total = $this->db->select("SELECT FOUND_ROWS() as total")[0]['total'];

        $experiences = array_map(fn ($row) => $this->mapRowToExperience($row), $results);

        return ['experiences' => $experiences, 'total' => (int)$total];
    }

    public function findById(int $id): ?Experience
    {
        $sql = "SELECT * FROM experiences WHERE id = :id AND deleted_at IS NULL";
        $results = $this->db->select($sql, [':id' => $id]);

        return $results ? $this->mapRowToExperience($results[0]) : null;
    }

    public function save(Experience $experience): Experience
    {
        $params = [
            ':title' => $experience->title,
            ':description' => $experience->description,
            ':start_date' => $experience->startDate->format('Y-m-d'),
            ':end_date' => $experience->endDate?->format('Y-m-d'),
            ':sort_order' => $experience->sortOrder,
        ];

        if ($experience->id) {
            $params[':id'] = $experience->id;
            $sql = "UPDATE experiences 
                    SET title = :title, description = :description, start_date = :start_date, end_date = :end_date, sort_order = :sort_order 
                    WHERE id = :id";
            $this->db->query($sql, $params);
            return $this->findById($experience->id);
        }

        $sql = "INSERT INTO experiences (title, description, start_date, end_date, sort_order) 
                VALUES (:title, :description, :start_date, :end_date, :sort_order)";
        $id = $this->db->insert($sql, $params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE experiences SET deleted_at = NOW() WHERE id = :id";
        $this->db->query($sql, [':id' => $id]);
        return true;
    }

    private function mapRowToExperience(array $row): Experience
    {
        return new Experience(
            title: $row['title'],
            description: $row['description'],
            startDate: new DateTimeImmutable($row['start_date']),
            endDate: $row['end_date'] ? new DateTimeImmutable($row['end_date']) : null,
            sortOrder: (int)$row['sort_order'],
            id: (int)$row['id'],
            createdAt: new DateTimeImmutable($row['created_at']),
            updatedAt: new DateTimeImmutable($row['updated_at']),
        );
    }
}
