<?php

declare(strict_types=1);

namespace App\Application\Service;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public function __construct(
        public private(set) string $secret,
        public private(set) string $algorithm = 'HS256',
    ) {
    }

    /**
     * Gera um token JWT para um usuário.
     */
    public function createToken(array $userData, string $subject = 'user-client', int $expiryTime = 3600): string
    {
        $now = new DateTimeImmutable();
        $payload = [
            'iss'  => 'portfolio-api', // Issuer
            'sub'  => $subject,        // Subject
            'iat'  => $now->getTimestamp(),
            'exp'  => $now->modify("+{$expiryTime} seconds")->getTimestamp(),
            'user' => $userData,
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Decodifica e valida um token JWT.
     * @throws \Exception Se o token for inválido ou expirado.
     */
    public function decodeToken(string $token): array
    {
        $decoded = (array) JWT::decode($token, new Key($this->secret, $this->algorithm));

        // Converter stdClass de 'user' para array se necessário
        if (isset($decoded['user']) && $decoded['user'] instanceof \stdClass) {
            $decoded['user'] = (array) $decoded['user'];
        }

        return $decoded;
    }

    /**
     * Gera um token público para acesso de visitantes.
     */
    public function generatePublicToken(): string
    {
        return $this->createToken([
            'name'      => 'Guest User',
            'role_name' => 'public',
        ], 'site-client');
    }

    /**
     * Gera um token privado para usuários autenticados.
     */
    public function generatePrivateToken(array $data): string
    {
        return $this->createToken([
            'id'        => $data['id'],
            'role_name' => $data['role_name'] ?? 'user',
        ], 'user-client');
    }
}
