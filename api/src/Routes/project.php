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

$app->get('/projects/page/{page}', function (Request $request, Response $response, array $args) {

  $page = $args['page'];

  $results = Project::getPage($page);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->post('/projects/save', function (Request $request, Response $response) {

  $data = $request->getParsedBody();

  $data['desimage'] = isset($_FILES['image']) ? $_FILES['image'] : null;

  $project = new Project();

  $project->setAttributes($data);
  
  $results = $project->save();

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