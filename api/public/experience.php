<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Experience;

$app->get('/experiences', function (Request $request, Response $response) {

  $result = Experience::list();

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->get('/experiences/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $result = Experience::get($id);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->post('/experiences/create', function (Request $request, Response $response) {

  $payload = $request->getParsedBody();

  $result = Experience::create($payload);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->put('/experiences/update/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $payload = $request->getParsedBody();

  $result = Experience::update($id, $payload);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->delete('/experiences/delete/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $result = Experience::delete($id);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});