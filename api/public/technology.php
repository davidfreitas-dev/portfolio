<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\Technology;

$app->get('/technologies', function (Request $request, Response $response) {

  $result = Technology::list();

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->get('/technologies/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $result = Technology::get($id);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->post('/technologies/create', function (Request $request, Response $response) {

  $payload = $request->getParsedBody();

  $result = Technology::create($payload);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->put('/technologies/update/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $payload = $request->getParsedBody();

  $result = Technology::update($id, $payload);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->delete('/technologies/delete/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $result = Technology::delete($id);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});