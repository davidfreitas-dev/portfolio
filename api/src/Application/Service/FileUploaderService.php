<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Exception\ValidationException;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class FileUploaderService
{
    private const array ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const array ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Uploads and validates a file.
     *
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

        // 2. Validate extension
        $clientFilename = (string)$file->getClientFilename();
        $extension = \strtolower(\pathinfo($clientFilename, PATHINFO_EXTENSION));

        if (!\in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new ValidationException('Extensão de arquivo não permitida.');
        }

        // 3. Validate MIME type (Server-side check)
        $tempFile = \tempnam(\sys_get_temp_dir(), 'upload_');
        $file->getStream()->rewind();
        \file_put_contents($tempFile, $file->getStream()->getContents());

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tempFile);
        \unlink($tempFile);

        if (!\in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new ValidationException('Tipo de arquivo inválido. Apenas imagens são permitidas.');
        }

        // 4. Generate secure filename
        $filename = \sprintf(
            '%s%s-%s.%s',
            $prefix !== '' && $prefix !== '0' ? $prefix . '-' : '',
            \time(),
            \bin2hex(
                \random_bytes(8),
            ),
            $extension,
        );

        $destinationPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;

        // Ensure directory exists
        if (!\is_dir($uploadPath) && (!\mkdir($uploadPath, 0o775, true) && !\is_dir($uploadPath))) {
            throw new RuntimeException(\sprintf('Diretório "%s" não pôde ser criado', $uploadPath));
        }

        // 5. Move file
        try {
            $file->moveTo($destinationPath);
        } catch (\Exception $e) {
            throw new RuntimeException('Falha ao mover o arquivo enviado.', 0, $e);
        }

        return $filename;
    }

    /**
     * Deletes a file from the upload path.
     *
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
}
