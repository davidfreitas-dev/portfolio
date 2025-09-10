<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Auth;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

$app->post('/signup', function (Request $request, Response $response) {

  $requestData = $request->getParsedBody();

  $jwt = Auth::signup($requestData);

  $apiResponse = ApiResponseFormatter::formatResponse(
    HTTPStatus::OK,
    "success",
    "Cadastro realizado com sucesso",
    $jwt
  );

  $response->getBody()->write(json_encode($apiResponse));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($apiResponse['code']);

});

$app->post('/signin', function (Request $request, Response $response) {

  $requestData = $request->getParsedBody();

  $jwt = Auth::signin($requestData['login'], $requestData['password']);

  $apiResponse = ApiResponseFormatter::formatResponse(
    HTTPStatus::OK,
    "success",
    "Usuário autenticado com sucesso",
    $jwt
  );

  $response->getBody()->write(json_encode($apiResponse));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($apiResponse['code']);

});

$app->post('/forgot', function (Request $request, Response $response) {

  $requestData = $request->getParsedBody();

  $recovery = Auth::getForgotLink($requestData['email']);

  $mailer = new \App\Mail\Mailer(); 
  
  $subject = "Redefinição de senha"; 
  
  $content = "<p>Olá <strong>{$recovery['user']['name']}</strong>,</p> <p>Recebemos uma solicitação para redefinir sua senha. Para continuar, clique no botão abaixo:</p> <div style='text-align: center; margin: 30px 0;'><a href=\"{$recovery['link']}\" style=\"display: inline-block; padding: 12px 28px; background-color: #038de7; color: #ffffff; text-decoration: none; border-radius: 12px;font-weight: bold;\">Redefinir Senha</a></div> <p>Se você não solicitou essa alteração, ignore este e-mail.</p>";

  $mailer->sendEmail(
    $recovery['user']['email'],
    $recovery['user']['name'],
    $subject,
    $content
  );

  $apiResponse = ApiResponseFormatter::formatResponse( 
    HTTPStatus::OK, 
    "success", 
    "Link de redefinição de senha enviado para o endereço de e-mail informado", 
    NULL 
  );

  $response->getBody()->write(json_encode($apiResponse));
  
  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($apiResponse['code']);

});

$app->post('/forgot/token', function (Request $request, Response $response) {

  $requestData = $request->getParsedBody();

  $tokenData = Auth::validateForgotLink($requestData['token']);

  $apiResponse = ApiResponseFormatter::formatResponse(
    HTTPStatus::OK,
    "success",
    "Token validado com sucesso",
    $tokenData
  );

  $response->getBody()->write(json_encode($apiResponse));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($apiResponse['code']);

});

$app->post('/forgot/reset', function (Request $request, Response $response) {

  $requestData = $request->getParsedBody();

  $tokenData = Auth::validateForgotLink($requestData['token']);

  Auth::setNewPassword($requestData['password'], $tokenData);

  $apiResponse = ApiResponseFormatter::formatResponse(
    HTTPStatus::OK,
    "success",
    "Senha redefinida com sucesso",
    NULL
  );

  $response->getBody()->write(json_encode($apiResponse));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($apiResponse['code']);

});