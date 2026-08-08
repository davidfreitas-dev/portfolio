<?php

declare(strict_types=1);

namespace App\Presentation\Action\Auth;

use App\Application\Service\JwtService;
use App\Presentation\Responder\JsonResponder;
use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RefreshAction
{
    public function __construct(
        private readonly JwtService $jwtService,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $cookies = $request->getCookieParams();
        $refreshToken = $cookies['refresh_token'] ?? null;

        if (!$refreshToken) {
            return $this->responder->error($response, "Refresh token não encontrado.", HTTPStatus::UNAUTHORIZED);
        }

        try {
            $decoded = $this->jwtService->decodeToken($refreshToken);

            if (!isset($decoded['user']['type']) || $decoded['user']['type'] !== 'refresh') {
                return $this->responder->error($response, "Token inválido.", HTTPStatus::UNAUTHORIZED);
            }

            $user = (array) $decoded['user'];

            // Gerar novo access token
            $newJwt = $this->jwtService->generatePrivateToken($user);

            // Opcional: Rotação de refresh token (gerar um novo a cada uso)
            $newRefreshToken = $this->jwtService->generateRefreshToken($user);

            $cookieString = 'refresh_token=' . urlencode($newRefreshToken) . '; HttpOnly; Secure; SameSite=Strict; Path=/auth; Max-Age=604800';
            $response = $response->withAddedHeader('Set-Cookie', $cookieString);

            return $this->responder->success($response, 'Token atualizado com sucesso.', [
                "token"      => $newJwt,
                "type"       => "Bearer",
                "expires_in" => 3600,
            ]);
        } catch (\Exception $e) {
            return $this->responder->error($response, "Sessão inválida ou expirada.", HTTPStatus::UNAUTHORIZED);
        }
    }
}
