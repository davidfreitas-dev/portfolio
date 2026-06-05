<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Contract\ErrorLogRepositoryInterface;
use App\Domain\Entity\ErrorLog;
use DateTimeImmutable;

class ErrorLogRepository implements ErrorLogRepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function save(ErrorLog $errorLog): ErrorLog
    {
        $sql = "INSERT INTO error_logs (level, message, context, resolved_at, resolved_by) 
                VALUES (:level, :message, :context, :resolved_at, :resolved_by)";
        
        $params = [
            ':level' => $errorLog->severity,
            ':message' => $errorLog->message,
            ':context' => json_encode($errorLog->context),
            ':resolved_at' => $errorLog->resolvedAt?->format('Y-m-d H:i:s'),
            ':resolved_by' => $errorLog->resolvedBy,
        ];

        $id = $this->db->execute($sql, $params);

        return new ErrorLog(
            severity: $errorLog->severity,
            message: $errorLog->message,
            context: $errorLog->context,
            resolvedAt: $errorLog->resolvedAt,
            resolvedBy: $errorLog->resolvedBy,
            createdAt: new DateTimeImmutable(),
            id: (int) $id
        );
    }

    public function markAsResolved(int $errorLogId, int $resolvedByUserId): bool
    {
        $sql = "UPDATE error_logs SET resolved_at = NOW(), resolved_by = :user_id WHERE id = :id";
        $this->db->execute($sql, [
            ':user_id' => $resolvedByUserId,
            ':id' => $errorLogId,
        ]);

        return true;
    }
}
