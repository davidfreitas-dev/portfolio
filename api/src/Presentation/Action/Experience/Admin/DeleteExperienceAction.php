<?php

declare(strict_types=1);

namespace App\Presentation\Action\Experience\Admin;

use App\Application\Service\ExperienceService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteExperienceAction
{
    public function __construct(
        private readonly ExperienceService $experienceService,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        $this->experienceService->deleteExperience($id);

        return $this->responder->success($response, 'Experiência removida com sucesso.', null, HTTPStatus::NO_CONTENT);
    }
}
