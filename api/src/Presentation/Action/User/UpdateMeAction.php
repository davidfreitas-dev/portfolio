<?php

declare(strict_types=1);

namespace App\Presentation\Action\User;

use App\Application\DTO\User\UpdateUserProfileRequestDTO;
use App\Application\Service\UserService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateMeAction
{
    public function __construct(
        private readonly UserService $userService,
        private readonly JsonResponder $responder,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $jwt = $request->getAttribute("jwt");
        $userId = (int)$jwt['user']['id'];

        $dto = new UpdateUserProfileRequestDTO();
        $dto->name = $data['name'] ?? '';
        $dto->email = $data['email'] ?? '';
        $dto->phone = $data['phone'] ?? null;

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->responder->error($response, $errors[0]->getMessage(), HTTPStatus::BAD_REQUEST);
        }

        $this->userService->updateProfile($userId, [
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
        ]);

        return $this->responder->success($response, 'Perfil atualizado com sucesso.');
    }
}
