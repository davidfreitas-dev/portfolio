<?php

declare(strict_types=1);

namespace App\Presentation\Action\Project\Admin;

use App\Application\Service\ProjectService;
use App\Presentation\Responder\JsonResponder;
use App\Presentation\Transformer\ProjectTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetProjectAction
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly ProjectTransformer $transformer,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        $project = $this->projectService->getProject($id);

        return $this->responder->success(
            $response,
            'Projeto recuperado com sucesso.',
            $this->transformer->transform($project),
        );
    }
}
