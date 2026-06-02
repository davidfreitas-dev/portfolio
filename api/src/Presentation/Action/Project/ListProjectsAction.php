<?php

declare(strict_types=1);

namespace App\Presentation\Action\Project;

use App\Application\Service\ProjectService;
use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListProjectsAction
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        $limit = (int)($queryParams['limit'] ?? 10);
        $search = (string)($queryParams['search'] ?? '');

        $projects = $this->projectService->listProjects($page, $limit, $search);

        return $this->responder->success($response, 'Projetos recuperados com sucesso.', $projects);
    }
}
