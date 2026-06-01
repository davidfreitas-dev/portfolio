<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Contract\OtpRepositoryInterface;
use App\Infrastructure\Persistence\Redis\RedisCache;

class OtpRepository implements OtpRepositoryInterface
{
    private const PREFIX = 'otp:';

    public function __construct(private readonly RedisCache $redis)
    {
    }

    public function create(string $email, string $otp, int $ttl): bool
    {
        return $this->redis->set(self::PREFIX . $email, $otp, $ttl);
    }

    public function verify(string $email, string $otp): bool
    {
        $storedOtp = $this->redis->get(self::PREFIX . $email);

        if ($storedOtp === $otp) {
            $this->invalidate($email);
            return true;
        }

        return false;
    }

    public function invalidate(string $email): bool
    {
        return $this->redis->delete(self::PREFIX . $email);
    }
}
