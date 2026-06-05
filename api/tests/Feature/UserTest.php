<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AppTestCase;

class UserTest extends AppTestCase
{
    public function test_can_get_own_profile(): void
    {
        $request = $this->createRequest('GET', '/users/me');
        $request = $this->withAdminToken($request);
        
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertEquals('Admin User', $envelope['data']['name']);
    }

    public function test_cannot_get_profile_without_token(): void
    {
        $request = $this->createRequest('GET', '/users/me');
        $response = $this->request($request);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
