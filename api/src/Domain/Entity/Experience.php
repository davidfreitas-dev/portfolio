<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use JsonSerializable;

class Experience implements JsonSerializable
{
    public function __construct(public string $title {
        set => trim($value);
    }, public private(set) string $description, public private(set) DateTimeImmutable $startDate, public private(set) ?DateTimeImmutable $endDate = null, public private(set) int $sortOrder = 0, public private(set) ?int $id = null, public private(set) ?DateTimeImmutable $createdAt = null, public private(set) ?DateTimeImmutable $updatedAt = null)
    {
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
