<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AppTestCase;

class AuthTest extends AppTestCase
{
    public function test_can_request_login_otp(): void
    {
        $data = ['email' => 'admin@example.com'];
        $request = $this->createJsonRequest('POST', '/auth/request-login', $data);
        $response = $this->request($request);

        // Mesmo se o email não existir, deve retornar 200 (silent failure)
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_cannot_login_with_invalid_credentials(): void
    {
        $data = [
            'email' => 'admin@example.com',
            'password' => 'wrong-password'
        ];
        $request = $this->createJsonRequest('POST', '/auth/login', $data);
        $response = $this->request($request);

        $this->assertEquals(401, $response->getStatusCode());
    }
}
