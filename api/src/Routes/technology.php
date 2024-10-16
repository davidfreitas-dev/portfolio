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

$app->post('/technologies/save', function (Request $request, Response $response) {

  $data = $request->getParsedBody();

  $data['desimage'] = isset($_FILES['image']) ? $_FILES['image'] : NULL;

  $technology = new Technology();

  $technology->setAttributes($data);

  if (!isset($data['idtechnology'])) {
    
    $results = $technology->create();

  } else {

    $results = $technology->update();

  }

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