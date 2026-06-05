<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Shared\Utility\StringHelper;
use DateTimeImmutable;

class Project
{
    public string $slug {
        set => $value !== '' && $value !== '0' ? StringHelper::slugify($value) : StringHelper::slugify($this->title);
    }

    public function __construct(
        public string $title {
            set => trim($value);
        },
        public private(set) string $description,
        ?string $slug = null,
        public private(set) ?string $summary = null,
        public private(set) ?string $image = null,
        public private(set) ?string $link = null,
        public private(set) ?string $githubLink = null,
        public private(set) int $sortOrder = 0,
        public private(set) bool $isActive = true,
        public private(set) array $technologies = [],
        public private(set) ?int $id = null,
        public private(set) ?DateTimeImmutable $createdAt = null,
        public private(set) ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->slug = $slug ?? $this->title;
    }
}
