<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Application\Service\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly JwtService $jwtService, private readonly array $ignore = [])
    {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $path = $request->getUri()->getPath();

        // Normalizar o path para garantir que comece com /
        $path = '/' . ltrim($path, '/');

        foreach ($this->ignore as $ignorePath) {
            if ($path === $ignorePath || str_starts_with($path, $ignorePath . '/')) {
                return $handler->handle($request);
            }
        }

        $authorization = $request->getHeaderLine('Authorization');

        if (empty($authorization)) {
            return $handler->handle($request);
        }

        if (!preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $this->unauthorized('Formato de token inválido.');
        }

        $token = $matches[1];

        try {
            $decoded = $this->jwtService->decodeToken($token);
            $request = $request->withAttribute('jwt', $decoded);
        } catch (\Exception $e) {
            return $this->unauthorized('Token inválido ou expirado: ' . $e->getMessage());
        }

        return $handler->handle($request);
    }

    private function unauthorized(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
