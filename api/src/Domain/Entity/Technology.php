<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Shared\Utility\StringHelper;
use DateTimeImmutable;
use JsonSerializable;

class Technology implements JsonSerializable
{
    public string $slug {
        set => $value !== '' && $value !== '0' ? StringHelper::slugify($value) : StringHelper::slugify($this->name);
    }

    public function __construct(
        public string $name {
            set => trim($value);
        },
        ?string $slug = null,
        public private(set) ?string $image = null,
        public private(set) int $sortOrder = 0,
        public private(set) ?int $id = null,
        public private(set) ?DateTimeImmutable $createdAt = null,
        public private(set) ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->slug = $slug ?? $this->name;
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
