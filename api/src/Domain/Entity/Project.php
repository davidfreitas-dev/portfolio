<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Shared\Utility\StringHelper;
use DateTimeImmutable;

class Project implements \JsonSerializable
{
    public private(set) ?int $id;
    public string $title {
        set => trim($value);
    }
    public string $slug {
        set => $value ? StringHelper::slugify($value) : StringHelper::slugify($this->title);
    }
    public private(set) ?string $summary;
    public private(set) string $description;
    public private(set) ?string $image;
    public private(set) ?string $link;
    public private(set) ?string $githubLink;
    public private(set) int $sortOrder;
    public private(set) bool $isActive;
    public private(set) array $technologies;
    public private(set) ?DateTimeImmutable $createdAt;
    public private(set) ?DateTimeImmutable $updatedAt;

    public function __construct(
        string $title,
        string $description,
        ?string $slug = null,
        ?string $summary = null,
        ?string $image = null,
        ?string $link = null,
        ?string $githubLink = null,
        int $sortOrder = 0,
        bool $isActive = true,
        array $technologies = [],
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->slug = $slug ?? $title;
        $this->summary = $summary;
        $this->image = $image;
        $this->link = $link;
        $this->githubLink = $githubLink;
        $this->sortOrder = $sortOrder;
        $this->isActive = $isActive;
        $this->technologies = $technologies;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
            'image' => $this->image,
            'link' => $this->link,
            'github_link' => $this->githubLink,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'technologies' => $this->technologies,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
