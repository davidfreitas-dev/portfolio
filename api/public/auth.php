<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Auth;

$app->post('/signup', function (Request $request, Response $response) {
 
  $data = $request->getParsedBody();

  $results = Auth::signup($data);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);
 
});

$app->post('/signin', function (Request $request, Response $response) {

  $data = $request->getParsedBody();

  $results = Auth::signin($data['deslogin'], $data['despassword']);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);

});

$app->post('/forgot', function (Request $request, Response $response) {
 
  $data = $request->getParsedBody();

  $results = Auth::getForgotLink($data['desemail']);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);
 
});

$app->post('/forgot/token', function (Request $request, Response $response) {
 
  $data = $request->getParsedBody();

  $results = Auth::validateForgotLink($data['code']);

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);
 
});

$app->post('/forgot/reset', function (Request $request, Response $response) {
 
  $data = $request->getParsedBody();

  $results = Auth::validateForgotLink($data['code']);

  if ($results['status'] == 'success') {

    Auth::setForgotUsed($results['data']['idrecovery']);

    $results = Auth::setNewPassword($data['despassword'], $results['data']['iduser']);

  } 

  $response->getBody()->write(json_encode($results));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($results['code']);
 
});