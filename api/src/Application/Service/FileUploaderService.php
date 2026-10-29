<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Exception\ValidationException;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class FileUploaderService
{
    private const array ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private const array MIME_TO_EXTENSION = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    private const int MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const int MAX_DIMENSION = 3840;            // 4K resolution limit (anti-pixel flood)
    private const int MAX_WEB_WIDTH = 1920;            // Max width for web optimization

    /**
     * Uploads, validates and sanitizes an image file.
     *
     * @param UploadedFileInterface $file
     * @param string $uploadPath Destination directory
     * @param string $prefix Prefix for the filename
     * @return string The generated filename
     * @throws ValidationException If validation fails
     */
    public function upload(UploadedFileInterface $file, string $uploadPath, string $prefix = ''): string
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new ValidationException('Falha no upload do arquivo.');
        }

        // 1. Validate size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new ValidationException('O arquivo excede o limite de tamanho de 5MB.');
        }

        // 2. Validate client extension
        $clientFilename = (string)$file->getClientFilename();
        $clientExtension = \strtolower(\pathinfo($clientFilename, PATHINFO_EXTENSION));

        if (!\in_array($clientExtension, self::ALLOWED_EXTENSIONS, true)) {
            throw new ValidationException('Extensão de arquivo não permitida.');
        }

        // 3. Read stream content for inspection
        $stream = $file->getStream();
        $stream->rewind();
        $contents = $stream->getContents();

        if ($contents === '') {
            throw new ValidationException('Arquivo enviado está vazio.');
        }

        // 4. Validate MIME type via finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($contents);

        if (!isset(self::MIME_TO_EXTENSION[$mimeType])) {
            throw new ValidationException('Tipo de arquivo inválido. Apenas imagens são permitidas.');
        }

        // 5. Structural image validation (anti-polyglot & anti-pixel flood)
        $imageInfo = @\getimagesizefromstring($contents);
        if ($imageInfo === false) {
            throw new ValidationException('Arquivo de imagem inválido ou corrompido.');
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            throw new ValidationException(\sprintf(
                'Dimensões da imagem excedem o limite de %d x %d pixels.',
                self::MAX_DIMENSION,
                self::MAX_DIMENSION,
            ));
        }

        // 6. Map secure extension from validated MIME type
        $extension = self::MIME_TO_EXTENSION[$mimeType];

        // 7. Generate secure filename
        $filename = \sprintf(
            '%s%s-%s.%s',
            $prefix !== '' && $prefix !== '0' ? $prefix . '-' : '',
            \time(),
            \bin2hex(\random_bytes(8)),
            $extension,
        );

        $destinationPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;

        // Ensure directory exists
        if (!\is_dir($uploadPath) && (!\mkdir($uploadPath, 0o775, true) && !\is_dir($uploadPath))) {
            throw new RuntimeException(\sprintf('Diretório "%s" não pôde ser criado', $uploadPath));
        }

        // 8. Move file
        try {
            $file->moveTo($destinationPath);
        } catch (\Exception $e) {
            throw new RuntimeException('Falha ao mover o arquivo enviado.', 0, $e);
        }

        // 9. Sanitize metadata (EXIF) and optimize web dimensions
        if (\file_exists($destinationPath) && \is_file($destinationPath)) {
            $this->sanitizeAndOptimize($destinationPath, $mimeType, $width, $height);
        }

        return $filename;
    }

    /**
     * Deletes a file from the upload path.
     *
     * @param string|null $filename
     * @param string $uploadPath Directory where the file is located
     */
    public function delete(?string $filename, string $uploadPath): void
    {
        if (!$filename) {
            return;
        }

        $basename = \basename($filename);
        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $basename;

        if (\file_exists($fullPath) && \is_file($fullPath)) {
            \unlink($fullPath);
        }
    }

    /**
     * Sanitizes image metadata (EXIF) and scales down excessive dimensions.
     */
    private function sanitizeAndOptimize(string $path, string $mimeType, int $width, int $height): void
    {
        if (!\extension_loaded('gd')) {
            return;
        }

        $image = match ($mimeType) {
            'image/jpeg' => @\imagecreatefromjpeg($path),
            'image/png'  => @\imagecreatefrompng($path),
            'image/gif'  => @\imagecreatefromgif($path),
            'image/webp' => \function_exists('imagecreatefromwebp') ? @\imagecreatefromwebp($path) : null,
            default      => null,
        };

        if ($image === null || $image === false) {
            return;
        }

        // Downscale if width exceeds MAX_WEB_WIDTH
        if ($width > self::MAX_WEB_WIDTH) {
            $newWidth = self::MAX_WEB_WIDTH;
            $newHeight = (int)\round(($height * $newWidth) / $width);
            $scaled = \imagescale($image, $newWidth, $newHeight, IMG_BILINEAR_FIXED);

            if ($scaled !== false) {
                if ($mimeType === 'image/png') {
                    \imagealphablending($scaled, false);
                    \imagesavealpha($scaled, true);
                }
                \imagedestroy($image);
                $image = $scaled;
            }
        }

        // Re-encode to strip EXIF metadata
        switch ($mimeType) {
            case 'image/jpeg':
                \imagejpeg($image, $path, 85);
                break;
            case 'image/png':
                \imagealphablending($image, false);
                \imagesavealpha($image, true);
                \imagepng($image, $path, 8);
                break;
            case 'image/gif':
                \imagegif($image, $path);
                break;
            case 'image/webp':
                if (\function_exists('imagewebp')) {
                    \imagewebp($image, $path, 85);
                }
                break;
        }

        \imagedestroy($image);
    }
}
