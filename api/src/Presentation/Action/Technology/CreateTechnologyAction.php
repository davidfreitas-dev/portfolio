<?php

declare(strict_types=1);

namespace App\Presentation\Action\Technology;

use App\Application\DTO\Technology\TechnologyRequestDTO;
use App\Application\Service\TechnologyService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateTechnologyAction
{
    public function __construct(
        private TechnologyService $service,
        private JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();
        $image = $files['image'] ?? null;

        $dto = new TechnologyRequestDTO(
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? null,
            sort_order: (int)($data['sort_order'] ?? 0),
            image: $image,
        );

        $technology = $this->service->createTechnology($dto);

        return $this->responder->success($response, 'Tecnologia criada com sucesso.', $technology, HTTPStatus::CREATED);
    }
}
