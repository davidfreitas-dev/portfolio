<?php

declare(strict_types=1);

namespace App\Presentation\Action\Contact;

use App\Application\DTO\ContactRequestDTO;
use App\Application\Service\ContactService;
use App\Presentation\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ContactAction
{
    public function __construct(
        private readonly ContactService $contactService,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $dto = ContactRequestDTO::fromArray($data ?? []);

        $errors = $dto->validate();
        if (!empty($errors)) {
            return $this->responder->respond($response, [
                'status' => 'error',
                'message' => 'Erro de validação',
                'errors' => $errors,
            ], 400);
        }

        try {
            $this->contactService->handleContactRequest($dto);
            return $this->responder->respond($response, [
                'status' => 'success',
                'message' => 'Mensagem enviada com sucesso!',
            ], 200);
        } catch (\Exception $e) {
            return $this->responder->respond($response, [
                'status' => 'error',
                'message' => 'Ocorreu um erro ao enviar sua mensagem. Tente novamente mais tarde.',
            ], 500);
        }
    }
}
