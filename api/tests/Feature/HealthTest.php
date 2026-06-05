<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AppTestCase;

class HealthTest extends AppTestCase
{
    public function test_health_endpoint_returns_success(): void
    {
        $request = $this->createRequest('GET', '/health');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals('ok', $data['services']['database']['status']);
        $this->assertEquals('ok', $data['services']['redis']['status']);
    }
}
