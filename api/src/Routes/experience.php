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

$app->get('/experiences/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $results = Experience::get($id);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->get('/experiences/search/{search}/{page}', function (Request $request, Response $response, array $args) {

  $page = $args['page'];
  
  $search = $args['search'];

  $results = Experience::getPageSearch($search, $page);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->get('/experiences/page/{page}', function (Request $request, Response $response, array $args) {

  $page = $args['page'];

  $results = Experience::getPage($page);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->post('/experiences/save', function (Request $request, Response $response) {

  $data = $request->getParsedBody();

  $experience = new Experience();

  $experience->setAttributes($data);

  if (!isset($data['idexperience'])) {
    
    $results = $experience->create();

  } else {

    $results = $experience->update();

  }

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