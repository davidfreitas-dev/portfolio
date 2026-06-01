<?php

declare(strict_types=1);

namespace App\Presentation\Action\User;

use App\Application\DTO\User\ChangePasswordRequestDTO;
use App\Application\Service\UserService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangePasswordAction
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

        $dto = new ChangePasswordRequestDTO();
        $dto->current_password = $data['current_password'] ?? '';
        $dto->new_password = $data['new_password'] ?? '';
        $dto->new_password_confirm = $data['new_password_confirm'] ?? '';

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->responder->error($response, $errors[0]->getMessage(), HTTPStatus::BAD_REQUEST);
        }

        try {
            $this->userService->changePassword($userId, $dto->current_password, $dto->new_password);
            return $this->responder->success($response, 'Senha alterada com sucesso.');
        } catch (\Exception $e) {
            return $this->responder->error($response, $e->getMessage(), (int) $e->getCode() ?: HTTPStatus::BAD_REQUEST);
        }
    }
}
