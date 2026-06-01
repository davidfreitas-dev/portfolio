<?php

declare(strict_types=1);

namespace App\Presentation\Action\Auth;

use App\Application\DTO\Auth\LoginPasswordRequestDTO;
use App\Application\Service\AuthService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginAction
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
        $email = $data['email'] ?? '';
        $otp = $data['otp'] ?? null;
        $password = $data['password'] ?? null;

        try {
            if ($otp) {
                $result = $this->authService->loginWithOtp($email, $otp);
            } elseif ($password) {
                $dto = new LoginPasswordRequestDTO();
                $dto->email = $email;
                $dto->password = $password;

                $errors = $this->validator->validate($dto);
                if (count($errors) > 0) {
                    return $this->responder->error($response, $errors[0]->getMessage(), HTTPStatus::BAD_REQUEST);
                }

                $result = $this->authService->loginWithPassword($email, $password);
            } else {
                return $this->responder->error($response, "Senha ou código OTP é obrigatório.", HTTPStatus::BAD_REQUEST);
            }

            return $this->responder->success($response, 'Login realizado com sucesso.', $result);
        } catch (\Exception $e) {
            return $this->responder->error($response, $e->getMessage(), (int) $e->getCode() ?: HTTPStatus::UNAUTHORIZED);
        }
    }
}
