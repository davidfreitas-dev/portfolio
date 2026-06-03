<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Shared\Utility\StringHelper;
use DateTimeImmutable;
use JsonSerializable;

class Technology implements JsonSerializable
{
    public private(set) ?int $id;
    public string $name {
        set => trim($value);
    }
    public string $slug {
        set => $value ? StringHelper::slugify($value) : StringHelper::slugify($this->name);
    }
    public private(set) ?string $image;
    public private(set) int $sortOrder;
    public private(set) ?DateTimeImmutable $createdAt;
    public private(set) ?DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        ?string $slug = null,
        ?string $image = null,
        int $sortOrder = 0,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug ?? $name;
        $this->image = $image;
        $this->sortOrder = $sortOrder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
