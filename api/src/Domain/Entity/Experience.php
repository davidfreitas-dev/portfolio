<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

class Experience
{
    public function __construct(public string $title {
        set => trim($value);
    }, public private(set) string $description, public private(set) DateTimeImmutable $startDate, public private(set) ?DateTimeImmutable $endDate = null, public private(set) int $sortOrder = 0, public private(set) ?int $id = null, public private(set) ?DateTimeImmutable $createdAt = null, public private(set) ?DateTimeImmutable $updatedAt = null)
    {
    }
}
