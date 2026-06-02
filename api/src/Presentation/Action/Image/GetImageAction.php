<?php

declare(strict_types=1);

namespace App\Presentation\Action\Image;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetImageAction
{
    private const array ALLOWED_FOLDERS = ['projects', 'technologies'];

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $folder = $args['folder'];
        $imageName = $args['image'];

        // 1. Segurança: Validar pasta
        if (!\in_array($folder, self::ALLOWED_FOLDERS, true)) {
            return $response->withStatus(404);
        }

        // 2. Segurança: Sanitizar nome do arquivo
        $imageName = \basename($imageName);

        $storagePath = $_ENV['STORAGE_PATH'] ?? (defined('APP_ROOT') ? APP_ROOT . '/storage' : __DIR__ . '/../../../../storage');
        $imagePath = $storagePath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $imageName;
        
        $isDefault = false;
        if (!\file_exists($imagePath) || !\is_file($imagePath)) {
            $imagePath = $storagePath . DIRECTORY_SEPARATOR . 'no-image.png';
            $isDefault = true;
        }

        if (!\file_exists($imagePath)) {
            return $response->withStatus(404);
        }

        // 3. Performance: Detectar MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imagePath);

        // 4. Cache: Cabeçalhos para o navegador
        $lastModified = \filemtime($imagePath);
        $etag = \md5($imagePath . $lastModified . \filesize($imagePath));

        if ($request->getHeaderLine('If-None-Match') === $etag || 
           ($request->getHeaderLine('If-Modified-Since') && \strtotime($request->getHeaderLine('If-Modified-Since')) === $lastModified)) {
            return $response->withStatus(304);
        }

        // 5. Profissional: X-Accel-Redirect
        // O caminho deve ser o PATH INTERNO definido no Nginx
        $internalRedirectPath = $isDefault 
            ? '/internal_static/no-image.png' 
            : '/internal_uploads/' . $folder . '/' . $imageName;

        return $response
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Cache-Control', 'public, max-age=31536000')
            ->withHeader('Last-Modified', \gmdate('D, d M Y H:i:s', $lastModified) . ' GMT')
            ->withHeader('ETag', $etag)
            ->withHeader('X-Accel-Redirect', $internalRedirectPath);
    }
}
