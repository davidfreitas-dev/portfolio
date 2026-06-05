<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AppTestCase;

class ProjectTest extends AppTestCase
{
    public function test_can_list_projects(): void
    {
        $request = $this->createRequest('GET', '/projects');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertArrayHasKey('projects', $envelope['data']);
        $this->assertArrayHasKey('pagination', $envelope['data']);
    }

    public function test_can_get_single_project(): void
    {
        $request = $this->createRequest('GET', '/projects/1');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertEquals(1, $envelope['data']['id']);
    }

    public function test_can_create_project_as_admin(): void
    {
        $uniqueSlug = 'project-' . uniqid();
        $data = [
            'title' => 'Feature Test Project ' . uniqid(),
            'description' => 'Test Description',
            'slug' => $uniqueSlug,
            'summary' => 'Summary',
            'sort_order' => 10
        ];

        $request = $this->createJsonRequest('POST', '/admin/projects', $data);
        $request = $this->withAdminToken($request);
        
        $response = $this->request($request);

        $this->assertEquals(201, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertStringContainsString('Feature Test Project', $envelope['data']['title']);
    }
}
