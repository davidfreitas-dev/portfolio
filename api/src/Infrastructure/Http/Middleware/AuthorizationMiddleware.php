<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @param string[] $allowedRoles
     */
    public function __construct(
        private readonly array $allowedRoles,
        private readonly JsonResponder $responder,
    ) {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $userRole = $request->getAttribute('user_role'); // Role name from JwtAuthMiddleware

        if (!$userRole) {
            return $this->responder->error(
                new SlimResponse(),
                'Falha na verificação de autorização. Função de usuário não encontrada no token.',
                HTTPStatus::FORBIDDEN,
            );
        }

        if ($this->allowedRoles === []) {
            return $this->responder->error(
                new SlimResponse(),
                'Falha na verificação de autorização. Funções permitidas não configuradas para esta rota.',
                HTTPStatus::INTERNAL_SERVER_ERROR,
            );
        }

        if (!\in_array($userRole, $this->allowedRoles, true)) {
            return $this->responder->error(
                new SlimResponse(),
                'Proibido: Permissões insuficientes.',
                HTTPStatus::FORBIDDEN,
            );
        }

        return $handler->handle($request);
    }
}
