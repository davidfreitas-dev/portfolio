<?php

declare(strict_types=1);

namespace App\Presentation\Action\Technology;

use App\Application\Service\TechnologyService;
use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListTechnologiesAction
{
    public function __construct(
        private readonly TechnologyService $service,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        $limit = (int)($queryParams['limit'] ?? 10);
        $search = (string)($queryParams['search'] ?? '');

        $technologies = $this->service->listTechnologies($page, $limit, $search);

        return $this->responder->success($response, 'Tecnologias recuperadas com sucesso.', $technologies);
    }
}
