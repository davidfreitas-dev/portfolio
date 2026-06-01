<?php

declare(strict_types=1);

namespace App\Presentation\Action\Project;

use App\Application\Service\ProjectService;
use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetProjectAction
{
    public function __construct(
        private ProjectService $projectService,
        private JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        // This method needs to be added to ProjectService
        $project = $this->projectService->getProject($id);

        return $this->responder->success($response, 'Projeto recuperado com sucesso.', $project);
    }
}
