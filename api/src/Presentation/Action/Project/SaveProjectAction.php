<?php

declare(strict_types=1);

namespace App\Presentation\Action\Project;

use App\Application\DTO\Project\ProjectRequestDTO;
use App\Application\Service\ProjectService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SaveProjectAction
{
    public function __construct(
        private ProjectService $projectService,
        private JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();
        $image = $files['image'] ?? null;
        
        $id = isset($data['id']) ? (int)$data['id'] : null;

        $dto = new ProjectRequestDTO(
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? null,
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? '',
            link: $data['link'] ?? null,
            github_link: $data['github_link'] ?? null,
            sort_order: (int)($data['sort_order'] ?? 0),
            is_active: (bool)($data['is_active'] ?? true),
            image: $image,
            technology_ids: (array)($data['technology_ids'] ?? []),
        );

        if ($id) {
            $project = $this->projectService->updateProject($id, $dto);
            $message = 'Projeto atualizado com sucesso.';
            $status = HTTPStatus::OK;
        } else {
            $project = $this->projectService->createProject($dto);
            $message = 'Projeto criado com sucesso.';
            $status = HTTPStatus::CREATED;
        }

        return $this->responder->success($response, $message, $project, $status);
    }
}
