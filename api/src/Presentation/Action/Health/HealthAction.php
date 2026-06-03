<?php

declare(strict_types=1);

namespace App\Presentation\Action\Health;

use App\Infrastructure\Persistence\Database;
use App\Presentation\Responder\JsonResponder;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Redis;

class HealthAction
{
    public function __construct(
        private readonly Database $database,
        private readonly Redis $redis,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $health = [
            'status' => 'success',
            'timestamp' => \date('Y-m-d H:i:s'),
            'services' => [
                'database' => $this->checkDatabase(),
                'redis' => $this->checkRedis(),
            ],
        ];

        $statusCode = 200;

        foreach ($health['services'] as $service) {
            if ($service['status'] !== 'ok') {
                $health['status'] = 'error';
                $statusCode = 503;
                break;
            }
        }

        return $this->responder->respond($response, $health, $statusCode);
    }

    private function checkDatabase(): array
    {
        try {
            $this->database->getConnection()->query('SELECT 1');
            return ['status' => 'ok'];
        } catch (Exception $exception) {
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            // ping() returns true or string "PONG" depending on the client version/config
            $this->redis->ping();
            return ['status' => 'ok'];
        } catch (Exception $exception) {
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }
    }
}
