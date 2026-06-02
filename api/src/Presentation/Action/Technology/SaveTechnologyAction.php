<?php

declare(strict_types=1);

namespace App\Presentation\Action\Technology;

use App\Application\DTO\Technology\TechnologyRequestDTO;
use App\Application\Service\TechnologyService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SaveTechnologyAction
{
    public function __construct(
        private TechnologyService $technologyService,
        private JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();
        $image = $files['image'] ?? null;
        
        $id = isset($data['id']) ? (int)$data['id'] : null;

        $dto = new TechnologyRequestDTO(
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? null,
            sort_order: (int)($data['sort_order'] ?? 0),
            image: $image,
        );

        if ($id) {
            $technology = $this->technologyService->updateTechnology($id, $dto);
            $message = 'Tecnologia atualizada com sucesso.';
            $status = HTTPStatus::OK;
        } else {
            $technology = $this->technologyService->createTechnology($dto);
            $message = 'Tecnologia criada com sucesso.';
            $status = HTTPStatus::CREATED;
        }

        return $this->responder->success($response, $message, $technology, $status);
    }
}
