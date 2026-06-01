<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response as SlimResponse;
use Throwable;

class ErrorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly JsonResponder $responder,
        private readonly LoggerInterface $logger,
        private readonly bool $displayErrorDetails = false,
    ) {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            return $this->handleException($e, $request);
        }
    }

    private function handleException(Throwable $e, Request $request): Response
    {
        $statusCode = $this->normalizeStatusCode($e->getCode());

        // Log the error with details
        $this->logger->error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'path' => $request->getUri()->getPath(),
            'method' => $request->getMethod(),
            'trace' => $this->displayErrorDetails ? $e->getTraceAsString() : 'Hidden',
        ]);

        $message = $e->getMessage();

        // Hide implementation details for 500 errors in production
        if ($statusCode >= 500 && !$this->displayErrorDetails) {
            $message = 'Ocorreu um erro interno no servidor.';
        }

        return $this->responder->error(new SlimResponse(), $message, $statusCode);
    }

    private function normalizeStatusCode(mixed $code): int
    {
        $code = (int) $code;
        return ($code >= 400 && $code < 600) ? $code : 500;
    }
}
