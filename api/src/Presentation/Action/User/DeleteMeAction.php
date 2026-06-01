<?php

declare(strict_types=1);

namespace App\Presentation\Action\User;

use App\Application\Service\UserService;
use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteMeAction
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

        $this->userService->deleteAccount($userId);

        return $this->responder->success($response, 'Conta deletada com sucesso.');
    }
}
