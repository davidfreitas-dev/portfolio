<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Persistence\Redis\RedisCache;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class HttpCacheInvalidationMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly RedisCache $cache)
    {
    }

    public function process(Request $request, Handler $handler): Response
    {
        $response = $handler->handle($request);

        $method = $request->getMethod();
        $statusCode = $response->getStatusCode();

        // Invalidate cache if a mutating request was successful (200, 201, 204)
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $statusCode < 400) {
            $this->invalidateCache($request);
        }

        return $response;
    }

    private function invalidateCache(Request $request): void
    {
        $path = trim($request->getUri()->getPath(), '/');
        $parts = explode('/', $path);

        // Identify the resource (e.g., admin/projects -> projects)
        // We assume admin group structure: /admin/{resource}
        $resource = $parts[1] ?? null;

        if ($resource) {
            // Pattern matches the one in HttpCacheMiddleware
            $pattern = sprintf('http:%s:*', $resource);
            $this->cache->deleteByPattern($pattern);
        }
    }
}
