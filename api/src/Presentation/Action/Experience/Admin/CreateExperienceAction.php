<?php

declare(strict_types=1);

namespace App\Presentation\Action\Experience\Admin;

use App\Application\DTO\Experience\ExperienceRequestDTO;
use App\Application\Service\ExperienceService;
use App\Presentation\Responder\JsonResponder;
use App\Presentation\Transformer\ExperienceTransformer;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateExperienceAction
{
    public function __construct(
        private readonly ExperienceService $experienceService,
        private readonly ExperienceTransformer $transformer,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];

        $dto = new ExperienceRequestDTO(
            title: $data['title'] ?? '',
            description: $data['description'] ?? '',
            start_date: $data['start_date'] ?? '',
            end_date: $data['end_date'] ?? null,
            sort_order: (int)($data['sort_order'] ?? 0),
        );

        $experience = $this->experienceService->createExperience($dto);

        return $this->responder->success(
            $response,
            'Experiência criada com sucesso.',
            $this->transformer->transform($experience),
            HTTPStatus::CREATED,
        );
    }
}
