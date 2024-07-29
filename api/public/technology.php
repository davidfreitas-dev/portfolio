<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Technology;

$app->get('/technologies', function (Request $request, Response $response) {

  $results = Technology::list();

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->get('/technologies/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $results = Technology::get($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->get('/technologies/page/{page}', function (Request $request, Response $response, array $args) {

  $page = $args['page'];

  $results = Technology::getPage($page);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->post('/technologies/create', function (Request $request, Response $response) {

  $payload = $request->getParsedBody();

  $results = Technology::create($payload);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->put('/technologies/update/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $payload = $request->getParsedBody();

  $results = Technology::update($id, $payload);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->delete('/technologies/delete/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $results = Technology::delete($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});