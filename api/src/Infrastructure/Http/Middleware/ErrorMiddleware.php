<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Presentation\Responder\JsonResponder;
use ErrorException;
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

    #[\Override]
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Capturar erros do PHP (como avisos e depreciações) e transformá-los em exceções
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $response = $handler->handle($request);
            restore_error_handler();

            return $response;
        } catch (Throwable $e) {
            restore_error_handler();

            return $this->handleException($e, $request);
        }
    }

    private function handleException(Throwable $e, Request $request): Response
    {
        $statusCode = $this->normalizeStatusCode($e->getCode());
        $isCritical = $statusCode >= 500;

        // Contexto para o log
        $context = [
            'exception' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'path' => $request->getUri()->getPath(),
            'method' => $request->getMethod(),
        ];

        if ($this->displayErrorDetails) {
            $context['trace'] = $e->getTraceAsString();
        }

        // Registrar o erro no log
        if ($isCritical) {
            $this->logger->critical($e->getMessage(), $context);
        } else {
            $this->logger->error($e->getMessage(), $context);
        }

        $message = $e->getMessage();

        // Em produção, ocultar detalhes de erros 500
        if ($isCritical && !$this->displayErrorDetails) {
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
