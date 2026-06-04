<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Application\Service\JwtService;
use App\Infrastructure\Persistence\Redis\RedisCache;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RedisCache $cache,
        private readonly JwtService $jwtService,
        private array $settings,
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if (!$this->settings['enabled']) {
            return $handler->handle($request);
        }

        $identifier = $this->getIdentifier($request);
        $key = 'rate_limit:' . $identifier;

        $maxRequests = $this->settings['max_requests'];
        $window = $this->settings['window'];

        // Get current count
        $current = $this->cache->get($key);

        if ($current === null) {
            // First request in this window
            $this->cache->set($key, 1, $window);
            $remaining = $maxRequests - 1;
            $resetTime = \time() + $window;
        } else {
            $remaining = $maxRequests - (int)$current;
            $resetTime = \time() + $this->cache->ttl($key);

            if ((int)$current >= $maxRequests) {
                // Rate limit exceeded
                return $this->buildRateLimitResponse($maxRequests, 0, $resetTime);
            }

            // Increment counter
            $this->cache->set($key, (int)$current + 1, $this->cache->ttl($key) ?: $window);
            --$remaining;
        }

        $response = $handler->handle($request);

        // Add rate limit headers
        return $response
            ->withHeader('X-RateLimit-Limit', (string)$maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string)\max(0, $remaining))
            ->withHeader('X-RateLimit-Reset', (string)$resetTime)
        ;
    }

    private function getIdentifier(ServerRequestInterface $request): string
    {
        // Try to get user ID from token (if authenticated)
        $token = $this->extractToken($request);
        if ($token) {
            try {
                $decodedToken = $this->jwtService->decodeToken($token);
                if ($decodedToken && isset($decodedToken['user']['id'])) {
                    // Use user ID (sub claim) as identifier
                    return 'user:' . $decodedToken['user']['id'];
                }
            } catch (Exception) {
                // Token is invalid or expired, fall through to IP-based rate limiting
            }
        }

        // Fallback to IP address
        $serverParams = $request->getServerParams();
        $remoteAddr = $serverParams['REMOTE_ADDR'] ?? 'unknown';

        // Only trust proxy headers if the remote address is a trusted proxy
        if (!empty($serverParams['HTTP_X_FORWARDED_FOR']) && $this->isTrustedProxy($remoteAddr)) {
            $ips = \explode(',', (string) $serverParams['HTTP_X_FORWARDED_FOR']);
            $ip = \trim($ips[0]);
        } else {
            $ip = $remoteAddr;
        }

        return 'ip:' . $ip;
    }

    private function isTrustedProxy(string $ip): bool
    {
        $trustedProxies = $this->settings['trusted_proxies'] ?? [];

        if (empty($trustedProxies)) {
            return false;
        }

        foreach ($trustedProxies as $trustedProxy) {
            if (\str_contains((string) $trustedProxy, '/')) {
                if ($this->ipInCidr($ip, $trustedProxy)) {
                    return true;
                }
            } elseif ($ip === $trustedProxy) {
                return true;
            }
        }

        return false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = \explode('/', $cidr);
        $mask = (int)$mask;

        if ($mask === 0) {
            return false;
        }

        $ipLong = \ip2long($ip);
        $subnetLong = \ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $netmask = ~((1 << (32 - $mask)) - 1);

        return ($ipLong & $netmask) === ($subnetLong & $netmask);
    }

    private function extractToken(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');

        if (\preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function buildRateLimitResponse(int $limit, int $remaining, int $reset): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(\json_encode([
            'error' => 'Excesso de Requisições',
            'message' => 'Limite de requisições excedido. Por favor, tente novamente mais tarde.',
            'retry_after' => $reset - \time(),
        ]));

        return $response
            ->withStatus(429)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-RateLimit-Limit', (string)$limit)
            ->withHeader('X-RateLimit-Remaining', (string)$remaining)
            ->withHeader('X-RateLimit-Reset', (string)$reset)
            ->withHeader('Retry-After', (string)($reset - \time()))
        ;
    }
}
