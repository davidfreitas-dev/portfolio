<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Project;

$app->get('/projects', function (Request $request, Response $response) {

  $results = Project::list();

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->get('/projects/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $results = Project::get($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->post('/projects/create', function (Request $request, Response $response) {

  $payload = $request->getParsedBody();

  $payload['image'] = isset($_FILES['photo']) ? $_FILES['photo'] : null;

  $results = Project::save($payload);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->delete('/projects/delete/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $results = Project::delete($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});