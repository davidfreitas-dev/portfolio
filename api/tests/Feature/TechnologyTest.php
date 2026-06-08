<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AppTestCase;

class TechnologyTest extends AppTestCase
{
    public function test_can_list_technologies(): void
    {
        $request = $this->createRequest('GET', '/public/technologies');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertArrayHasKey('technologies', $envelope['data']);
        $this->assertArrayHasKey('pagination', $envelope['data']);
    }

    public function test_can_get_single_technology(): void
    {
        $request = $this->createRequest('GET', '/public/technologies/1');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertEquals(1, $envelope['data']['id']);
    }

    public function test_can_create_technology_as_admin(): void
    {
        $uniqueSlug = 'tech-' . uniqid();
        $data = [
            'name' => 'Feature Test Tech ' . uniqid(),
            'slug' => $uniqueSlug,
            'sort_order' => 10
        ];

        $request = $this->createJsonRequest('POST', '/admin/technologies', $data);
        $request = $this->withAdminToken($request);
        
        $response = $this->request($request);

        $this->assertEquals(201, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertStringContainsString('Feature Test Tech', $envelope['data']['name']);
    }
}
