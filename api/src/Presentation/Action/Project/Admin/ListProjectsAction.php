<?php

declare(strict_types=1);

namespace App\Presentation\Action\Project\Admin;

use App\Application\Service\ProjectService;
use App\Presentation\Responder\JsonResponder;
use App\Presentation\Transformer\ProjectTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListProjectsAction
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly ProjectTransformer $transformer,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        $limit = (int)($queryParams['limit'] ?? 10);
        $search = (string)($queryParams['search'] ?? '');
        $isActive = isset($queryParams['is_active']) ? (string)$queryParams['is_active'] === '1' : null;

        $result = $this->projectService->listProjects($page, $limit, $search, $isActive);

        $data = [
            'projects' => $this->transformer->transformCollection($result['projects']),
            'pagination' => [
                'total_items' => $result['total_items'],
                'current_page' => $result['current_page'],
                'items_per_page' => $result['items_per_page'],
                'total_pages' => $result['total_pages'],
            ],
        ];

        return $this->responder->success($response, 'Projetos recuperados com sucesso.', $data);
    }
}
