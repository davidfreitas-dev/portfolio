<?php

declare(strict_types=1);

namespace App\Presentation\Action\Technology;

use App\Application\Service\TechnologyService;
use App\Presentation\Responder\JsonResponder;
use App\Presentation\Transformer\TechnologyTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListTechnologiesAction
{
    public function __construct(
        private readonly TechnologyService $service,
        private readonly TechnologyTransformer $transformer,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        $limit = (int)($queryParams['limit'] ?? 10);
        $search = (string)($queryParams['search'] ?? '');

        $result = $this->service->listTechnologies($page, $limit, $search);

        $data = [
            'technologies' => $this->transformer->transformCollection($result['technologies']),
            'pagination' => [
                'total_items' => $result['total_items'],
                'current_page' => $result['current_page'],
                'items_per_page' => $result['items_per_page'],
                'total_pages' => $result['total_pages'],
            ],
        ];

        return $this->responder->success($response, 'Tecnologias recuperadas com sucesso.', $data);
    }
}
