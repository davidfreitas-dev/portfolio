<?php

declare(strict_types=1);

namespace App\Presentation\Action\Experience;

use App\Application\Service\ExperienceService;
use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetExperienceAction
{
    public function __construct(
        private ExperienceService $experienceService,
        private JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $experience = $this->experienceService->getExperience($id);

        return $this->responder->success($response, 'Experiência recuperada com sucesso.', $experience);
    }
}
