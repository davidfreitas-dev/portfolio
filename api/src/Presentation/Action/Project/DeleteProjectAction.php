<?php

declare(strict_types=1);

namespace App\Presentation\Action\Project;

use App\Application\Service\ProjectService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteProjectAction
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        $this->projectService->deleteProject($id);

        return $this->responder->success($response, 'Projeto removido com sucesso.', null, HTTPStatus::NO_CONTENT);
    }
}
