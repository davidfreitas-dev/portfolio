<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Shared\Utility\StringHelper;
use DateTimeImmutable;

class Technology
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
}
