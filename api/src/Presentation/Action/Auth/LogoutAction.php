<?php

declare(strict_types=1);

namespace App\Presentation\Action\Auth;

use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LogoutAction
{
    public function __construct(
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // For JWT-based authentication, logout is typically handled client-side.
        // This endpoint can be used to perform server-side cleanup if needed in the future.
        return $this->responder->success($response, 'Logout realizado com sucesso.');
    }
}
