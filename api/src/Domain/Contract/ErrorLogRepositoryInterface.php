<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\Entity\ErrorLog;

interface ErrorLogRepositoryInterface
{
    public function save(ErrorLog $errorLog): ErrorLog;
    public function markAsResolved(int $errorLogId, int $resolvedByUserId): bool;
}
