<?php

declare(strict_types=1);

namespace App\Presentation\Action\Technology\Admin;

use App\Application\Service\TechnologyService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteTechnologyAction
{
    public function __construct(
        private readonly TechnologyService $technologyService,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        $this->technologyService->deleteTechnology($id);

        return $this->responder->success($response, 'Tecnologia removida com sucesso.', null, HTTPStatus::NO_CONTENT);
    }
}
