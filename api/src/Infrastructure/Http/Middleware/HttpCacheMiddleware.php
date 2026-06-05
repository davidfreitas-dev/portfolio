<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Persistence\Redis\RedisCache;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class HttpCacheMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RedisCache $cache,
        private readonly int $ttl = 3600, // Default 1 hour
    ) {
    }

    public function process(Request $request, Handler $handler): Response
    {
        // Only cache GET requests
        if ($request->getMethod() !== 'GET') {
            return $handler->handle($request);
        }

        $cacheKey = $this->generateCacheKey($request);

        // Try to get from cache
        $cachedResponse = $this->cache->get($cacheKey);
        if ($cachedResponse !== null) {
            $response = new SlimResponse();
            $response->getBody()->write($cachedResponse['body']);

            foreach ($cachedResponse['headers'] as $name => $values) {
                $response = $response->withHeader($name, $values);
            }

            return $response->withHeader('X-Cache', 'HIT');
        }

        // If not in cache, proceed
        $response = $handler->handle($request);

        // Only cache successful responses
        if ($response->getStatusCode() === 200) {
            $this->cacheResponse($cacheKey, $response);
        }

        return $response->withHeader('X-Cache', 'MISS');
    }

    private function generateCacheKey(Request $request): string
    {
        $uri = $request->getUri();
        $path = trim($uri->getPath(), '/');
        $query = $uri->getQuery();

        // Example: http:public/projects:hash_of_query
        $resource = explode('/', $path)[1] ?? 'general';
        $queryHash = md5($query);

        return sprintf('http:%s:%s:%s', $resource, str_replace('/', '_', $path), $queryHash);
    }

    private function cacheResponse(string $key, Response $response): void
    {
        $data = [
            'body' => (string)$response->getBody(),
            'headers' => $response->getHeaders(),
        ];

        $this->cache->set($key, $data, $this->ttl);
    }
}
