<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use JsonSerializable;

class Experience implements JsonSerializable
{
    public private(set) ?int $id;
    public string $title {
        set => trim($value);
    }
    public private(set) string $description;
    public private(set) DateTimeImmutable $startDate;
    public private(set) ?DateTimeImmutable $endDate;
    public private(set) int $sortOrder;
    public private(set) ?DateTimeImmutable $createdAt;
    public private(set) ?DateTimeImmutable $updatedAt;

    public function __construct(
        string $title,
        string $description,
        DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate = null,
        int $sortOrder = 0,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
