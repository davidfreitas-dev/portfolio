<?php

declare(strict_types=1);

namespace App\Presentation\Action\Technology;

use App\Application\Service\TechnologyService;
use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetTechnologyAction
{
    public function __construct(
        private readonly TechnologyService $service,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $technology = $this->service->getTechnology($id);

        return $this->responder->success($response, 'Tecnologia recuperada com sucesso.', $technology);
    }
}
