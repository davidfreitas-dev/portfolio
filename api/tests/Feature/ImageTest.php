<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AppTestCase;

class ImageTest extends AppTestCase
{
    private string $storagePath;
    private string $testImagePath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->storagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'portfolio_storage';
        $_ENV['STORAGE_PATH'] = $this->storagePath;
        
        $uploadDir = $this->storagePath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'projects';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        
        $this->testImagePath = $uploadDir . DIRECTORY_SEPARATOR . 'test.png';
        file_put_contents($this->testImagePath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        
        // Create no-image.png in storage root
        file_put_contents($this->storagePath . DIRECTORY_SEPARATOR . 'no-image.png', 'no-image content');
    }

    protected function tearDown(): void
    {
        if (is_dir($this->storagePath)) {
            $this->removeDirectory($this->storagePath);
        }
        parent::tearDown();
    }

    private function removeDirectory(string $dir): void
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    public function test_can_get_existing_image(): void
    {
        $request = $this->createRequest('GET', '/images/projects/test.png');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('image/png', $response->getHeaderLine('Content-Type'));
        $this->assertTrue($response->hasHeader('X-Accel-Redirect'));
        $this->assertStringContainsString('/internal_uploads/projects/test.png', $response->getHeaderLine('X-Accel-Redirect'));
    }

    public function test_returns_no_image_if_not_found(): void
    {
        $request = $this->createRequest('GET', '/images/projects/non-existent.png');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());
        // In the real app it returns no-image.png
        $this->assertStringContainsString('no-image.png', $response->getHeaderLine('X-Accel-Redirect'));
    }

    public function test_returns_404_for_invalid_folder(): void
    {
        $request = $this->createRequest('GET', '/images/invalid-folder/test.png');
        $response = $this->request($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_handles_cache_headers(): void
    {
        $request = $this->createRequest('GET', '/images/projects/test.png');
        $response = $this->request($request);
        
        $etag = $response->getHeaderLine('ETag');
        
        $request304 = $this->createRequest('GET', '/images/projects/test.png', [
            'If-None-Match' => $etag
        ]);
        $response304 = $this->request($request304);
        
        $this->assertEquals(304, $response304->getStatusCode());
    }
}
