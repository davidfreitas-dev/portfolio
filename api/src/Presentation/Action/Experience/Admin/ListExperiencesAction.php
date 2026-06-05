<?php

declare(strict_types=1);

namespace App\Presentation\Action\Experience\Admin;

use App\Application\Service\ExperienceService;
use App\Presentation\Responder\JsonResponder;
use App\Presentation\Transformer\ExperienceTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListExperiencesAction
{
    public function __construct(
        private readonly ExperienceService $experienceService,
        private readonly ExperienceTransformer $transformer,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        $limit = (int)($queryParams['limit'] ?? 10);
        $search = (string)($queryParams['search'] ?? '');

        $result = $this->experienceService->listExperiences($page, $limit, $search);

        $data = [
            'experiences' => $this->transformer->transformCollection($result['experiences']),
            'pagination' => [
                'total_items' => $result['total_items'],
                'current_page' => $result['current_page'],
                'items_per_page' => $result['items_per_page'],
                'total_pages' => $result['total_pages'],
            ],
        ];

        return $this->responder->success($response, 'Experiências recuperadas com sucesso.', $data);
    }
}
