<?php

declare(strict_types=1);

namespace App\Presentation\Action\User;

use App\Application\Service\UserService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetMeAction
{
    public function __construct(
        private readonly UserService $userService,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $jwt = $request->getAttribute("jwt");
        $userId = (int)$jwt['user']['id'];

        $user = $this->userService->findById($userId);

        if (!$user) {
            return $this->responder->error($response, "Usuário não encontrado.", HTTPStatus::NOT_FOUND);
        }

        return $this->responder->success($response, 'Dados recuperados com sucesso.', $user);
    }
}
