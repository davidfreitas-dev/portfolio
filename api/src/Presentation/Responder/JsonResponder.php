<?php

declare(strict_types=1);

namespace App\Presentation\Responder;

use App\Shared\Enum\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;

class JsonResponder
{
    public function respond(Response $response, mixed $data, int $status = HTTPStatus::OK): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    public function success(Response $response, string $message, mixed $data = null, int $status = HTTPStatus::OK): Response
    {
        $payload = [
            'code' => $status,
            'status' => 'success',
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return $this->respond($response, $payload, $status);
    }

    public function error(Response $response, string $message, int $status = HTTPStatus::BAD_REQUEST): Response
    {
        return $this->respond($response, [
            'code' => $status,
            'status' => 'error',
            'message' => $message,
        ], $status);
    }
}
