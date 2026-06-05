<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Service\FileUploaderService;
use App\Domain\Exception\ValidationException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

beforeEach(function () {
    $this->service = new FileUploaderService();
    $this->uploadPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_uploads';
    if (!is_dir($this->uploadPath)) {
        mkdir($this->uploadPath, 0775, true);
    }
});

afterEach(function () {
    // Clean up
    if (is_dir($this->uploadPath)) {
        $files = glob($this->uploadPath . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        rmdir($this->uploadPath);
    }
    \Mockery::close();
});

test('should upload a valid image', function () {
    $file = \Mockery::mock(UploadedFileInterface::class);
    $stream = \Mockery::mock(StreamInterface::class);
    
    // Create a real small transparent PNG in memory to satisfy finfo
    $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

    $file->shouldReceive('getError')->andReturn(UPLOAD_ERR_OK);
    $file->shouldReceive('getSize')->andReturn(1024);
    $file->shouldReceive('getClientFilename')->andReturn('test.png');
    $file->shouldReceive('getStream')->andReturn($stream);
    
    $stream->shouldReceive('rewind')->andReturnNull();
    $stream->shouldReceive('getContents')->andReturn($pngContent);
    
    $file->shouldReceive('moveTo')->once()->with(\Mockery::on(function($path) {
        return str_contains($path, $this->uploadPath);
    }));

    $filename = $this->service->upload($file, $this->uploadPath, 'test-prefix');

    expect($filename)->toContain('test-prefix-')
        ->and($filename)->toEndWith('.png');
});

test('should throw validation exception if upload error', function () {
    $file = \Mockery::mock(UploadedFileInterface::class);
    $file->shouldReceive('getError')->andReturn(UPLOAD_ERR_INI_SIZE);

    $this->service->upload($file, $this->uploadPath);
})->throws(ValidationException::class, 'Falha no upload do arquivo.');

test('should throw validation exception if file too large', function () {
    $file = \Mockery::mock(UploadedFileInterface::class);
    $file->shouldReceive('getError')->andReturn(UPLOAD_ERR_OK);
    $file->shouldReceive('getSize')->andReturn(10 * 1024 * 1024); // 10MB

    $this->service->upload($file, $this->uploadPath);
})->throws(ValidationException::class, 'O arquivo excede o limite de tamanho de 5MB.');

test('should throw validation exception if invalid extension', function () {
    $file = \Mockery::mock(UploadedFileInterface::class);
    $file->shouldReceive('getError')->andReturn(UPLOAD_ERR_OK);
    $file->shouldReceive('getSize')->andReturn(1024);
    $file->shouldReceive('getClientFilename')->andReturn('test.exe');

    $this->service->upload($file, $this->uploadPath);
})->throws(ValidationException::class, 'Extensão de arquivo não permitida.');

test('should throw validation exception if invalid mime type', function () {
    $file = \Mockery::mock(UploadedFileInterface::class);
    $stream = \Mockery::mock(StreamInterface::class);
    
    $file->shouldReceive('getError')->andReturn(UPLOAD_ERR_OK);
    $file->shouldReceive('getSize')->andReturn(1024);
    $file->shouldReceive('getClientFilename')->andReturn('test.png');
    $file->shouldReceive('getStream')->andReturn($stream);
    
    $stream->shouldReceive('rewind')->andReturnNull();
    $stream->shouldReceive('getContents')->andReturn('not a png content');

    $this->service->upload($file, $this->uploadPath);
})->throws(ValidationException::class, 'Tipo de arquivo inválido. Apenas imagens são permitidas.');

test('should delete a file', function () {
    $filename = 'to_delete.png';
    $fullPath = $this->uploadPath . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($fullPath, 'content');

    expect(file_exists($fullPath))->toBeTrue();

    $this->service->delete($filename, $this->uploadPath);

    expect(file_exists($fullPath))->toBeFalse();
});

test('should do nothing when deleting non-existent file', function () {
    $this->service->delete('non_existent.png', $this->uploadPath);
    // Should not throw exception
    expect(true)->toBeTrue();
});
