<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', function (Request $request, Response $response) {

  $welcomeMessage = [
    'message' => 'Welcome to the Personal Portfolio Site API!'
  ];

  $response->getBody()->write(json_encode($welcomeMessage));

  return $response->withHeader('content-type', 'application/json');

});

$app->get('/images/{folder}/{image}', function (Request $request, Response $response, array $args) {

  $imageDirectoryPath = '/../res/img/';

  $imagePath = __DIR__ . $imageDirectoryPath . $args['folder'] . '/' . $args['image'];

  if (!file_exists($imagePath)) {

    $imagePath = __DIR__ . $imageDirectoryPath . '/no-image.png';

  }

  $image = file_get_contents($imagePath);

  $response->getBody()->write($image);

  return $response->withHeader('Content-Type', 'image/jpeg');

});