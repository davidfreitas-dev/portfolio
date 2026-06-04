<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AppTestCase;

class ExperienceTest extends AppTestCase
{
    public function test_can_list_experiences(): void
    {
        $request = $this->createRequest('GET', '/experiences');
        $response = $this->request($request);

        $body = (string)$response->getBody();
        $envelope = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($envelope, "Response body is not a valid JSON: " . $body);
        $this->assertEquals('success', $envelope['status']);
        
        $data = $envelope['data'];
        $this->assertArrayHasKey('experiences', $data);
        $this->assertArrayHasKey('total_items', $data);
        $this->assertGreaterThanOrEqual(1, count($data['experiences']));
    }

    public function test_can_get_single_experience(): void
    {
        // Assumindo que o seed inseriu pelo menos uma experiência com ID 1
        $request = $this->createRequest('GET', '/experiences/1');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        
        $data = $envelope['data'];
        $this->assertEquals(1, $data['id']);
        $this->assertArrayHasKey('title', $data);
    }

    public function test_returns_404_for_non_existent_experience(): void
    {
        $request = $this->createRequest('GET', '/experiences/9999');
        $response = $this->request($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_can_create_experience_as_admin(): void
    {
        $data = [
            'title' => 'New Test Experience',
            'description' => 'Test Description',
            'start_date' => '2024-01-01',
            'end_date' => null,
            'sort_order' => 5
        ];

        $request = $this->createJsonRequest('POST', '/experiences', $data);
        $request = $this->withAdminToken($request);
        
        $response = $this->request($request);

        $this->assertEquals(201, $response->getStatusCode());
        
        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertEquals('New Test Experience', $envelope['data']['title']);
    }

    public function test_cannot_create_experience_without_token(): void
    {
        $data = [
            'title' => 'New Test Experience',
            'description' => 'Test Description',
            'start_date' => '2024-01-01',
            'sort_order' => 5
        ];

        $request = $this->createJsonRequest('POST', '/experiences', $data);
        $response = $this->request($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_can_delete_experience_as_admin(): void
    {
        // Primeiro cria uma para deletar
        $token = $this->createAdminToken();
        $createData = [
            'title' => 'To be deleted',
            'description' => 'Delete me',
            'start_date' => '2024-01-01',
            'sort_order' => 99
        ];
        
        $createRequest = $this->createJsonRequest('POST', '/experiences', $createData)
            ->withHeader('Authorization', 'Bearer ' . $token);
        $createResponse = $this->request($createRequest);
        $createdId = json_decode((string)$createResponse->getBody(), true)['data']['id'];

        // Agora deleta
        $deleteRequest = $this->createRequest('DELETE', "/experiences/$createdId")
            ->withHeader('Authorization', 'Bearer ' . $token);
        $deleteResponse = $this->request($deleteRequest);

        $this->assertEquals(200, $responseCode = $deleteResponse->getStatusCode());
        
        // Verifica se foi deletado (logical delete)
        $getRequest = $this->createRequest('GET', "/experiences/$createdId");
        $getResponse = $this->request($getRequest);
        $this->assertEquals(404, $getResponse->getStatusCode());
    }
}
