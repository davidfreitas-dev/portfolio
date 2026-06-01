<?php

declare(strict_types=1);

namespace App\Presentation\Action\Experience;

use App\Application\Service\ExperienceService;
use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListExperiencesAction
{
    public function __construct(
        private ExperienceService $experienceService,
        private JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        $limit = (int)($queryParams['limit'] ?? 10);
        $search = (string)($queryParams['search'] ?? '');

        $experiences = $this->experienceService->listExperiences($page, $limit, $search);

        return $this->responder->success($response, 'Experiências recuperadas com sucesso.', $experiences);
    }
}
