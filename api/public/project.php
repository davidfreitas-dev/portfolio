<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\Project;

$app->get('/projects', function (Request $request, Response $response) {

  $result = Project::list();

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->get('/projects/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $result = Project::get($id);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->post('/projects/save', function (Request $request, Response $response) {

  $payload = $request->getParsedBody();

  $payload['image'] = isset($_FILES['photo']) ? $_FILES['photo'] : null;

  $result = Project::save($payload);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});

$app->delete('/projects/delete/{id}', function (Request $request, Response $response) {

  $id = $request->getAttribute('id');

  $result = Project::delete($id);

  $response->getBody()->write(json_encode($result));

  return $response->withHeader('content-type', 'application/json');

});