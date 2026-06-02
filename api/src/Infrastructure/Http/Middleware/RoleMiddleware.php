<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware
{
    private readonly array $requiredRoles;

    /**
     * Construtor que recebe um ou mais roles permitidos.
     */
    public function __construct(string|array $roles)
    {
        $this->requiredRoles = (array) $roles;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $jwt = $request->getAttribute('jwt');

        if (!$jwt || !isset($jwt['user']['role_name'])) {
            return $this->deny('Acesso negado. Nenhuma permissão encontrada.');
        }

        $userRole = $jwt['user']['role_name'];

        $hasAccess = in_array($userRole, $this->requiredRoles, true);

        if (!$hasAccess) {
            return $this->deny(
                'Acesso negado. Esta ação requer uma das seguintes permissões: ' . implode(', ', $this->requiredRoles),
            );
        }

        return $handler->handle($request);
    }

    private function deny(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(HTTPStatus::FORBIDDEN);
    }
}
