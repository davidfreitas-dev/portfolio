<?php

declare(strict_types=1);

namespace App\Presentation\Action\Auth;

use App\Application\DTO\Auth\ResetPasswordRequestDTO;
use App\Application\Service\AuthService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetPasswordAction
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly JsonResponder $responder,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $dto = new ResetPasswordRequestDTO();
        $dto->email = $data['email'] ?? '';
        $dto->code = $data['code'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->password_confirm = $data['password_confirm'] ?? '';

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->responder->error($response, $errors[0]->getMessage(), HTTPStatus::BAD_REQUEST);
        }

        try {
            $this->authService->resetPassword($dto->email, $dto->code, $dto->password);
            return $this->responder->success($response, 'Senha alterada com sucesso.');
        } catch (\Exception $e) {
            return $this->responder->error($response, $e->getMessage(), (int) $e->getCode() ?: HTTPStatus::BAD_REQUEST);
        }
    }
}
