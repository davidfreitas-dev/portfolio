<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use JsonSerializable;

class ErrorLog implements JsonSerializable
{
    public function __construct(
        public readonly string $severity,
        public readonly string $message,
        public readonly array $context = [],
        public readonly ?DateTimeImmutable $resolvedAt = null,
        public readonly ?int $resolvedBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?int $id = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'severity' => $this->severity,
            'message' => $this->message,
            'context' => $this->context,
            'resolved_at' => $this->resolvedAt?->format('Y-m-d H:i:s'),
            'resolved_by' => $this->resolvedBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
