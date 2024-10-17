<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/images/{folder}/{image}', function (Request $request, Response $response, array $args) {

  $imageDirectoryPath = '/../../storage/';

  $defaultImage = 'no-image.png';

  $imagePath = __DIR__ . $imageDirectoryPath . $args['folder'] . '/' . $args['image'];

  if (!file_exists($imagePath)) {

    $imagePath = __DIR__ . $imageDirectoryPath . $defaultImage;

  }

  $image = file_get_contents($imagePath);

  $response->getBody()->write($image);

  return $response->withHeader('Content-Type', 'image/jpeg');

});