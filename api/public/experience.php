<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Experience;

$app->get('/experiences', function (Request $request, Response $response) {

  $results = Experience::list();

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->get('/experiences/{page}', function (Request $request, Response $response, array $args) {

  $page = $args['page'];

  $results = Experience::getPage($page);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->get('/experiences/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $results = Experience::get($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->post('/experiences/create', function (Request $request, Response $response) {

  $payload = $request->getParsedBody();

  $results = Experience::create($payload);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->put('/experiences/update/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $payload = $request->getParsedBody();

  $results = Experience::update($id, $payload);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->delete('/experiences/delete/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $results = Experience::delete($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});