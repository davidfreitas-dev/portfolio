<?php

use App\Mail\Mailer;
use App\Services\AuthService;
use App\Services\MailService;
use App\Services\TokenService;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/auth', function ($group) {

  $group->post('/signup', function (Request $request, Response $response) {

    $requestData = $request->getParsedBody();

    $authService = new AuthService(
      new \App\DB\Database(),
      new \App\Services\TokenService()
    );

    $jwt = $authService->signup($requestData);

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

  $group->post('/signin', function (Request $request, Response $response) {

    $requestData = $request->getParsedBody();

    $authService = new AuthService(
      new \App\DB\Database(),
      new \App\Services\TokenService()
    );

    $jwt = $authService->signin($requestData['login'], $requestData['password']);

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

  $group->post('/forgot', function (Request $request, Response $response) {

    $requestData = $request->getParsedBody();

    $authService = new AuthService(
      new \App\DB\Database(),
      new \App\Services\TokenService()
    );

    $recovery = $authService->getForgotLink($requestData['email']);

    $emailService = new MailService(new Mailer());

    $emailService->sendPasswordReset(
      $recovery['user']['email'],
      $recovery['user']['name'],
      $recovery['link']
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

  $group->post('/verify', function (Request $request, Response $response) {

    $requestData = $request->getParsedBody();

    $authService = new AuthService(
      new \App\DB\Database(),
      new \App\Services\TokenService()
    );

    $tokenData = $authService->validateForgotLink($requestData['token']);

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

  $group->post('/reset', function (Request $request, Response $response) {

    $requestData = $request->getParsedBody();

    $authService = new AuthService(
      new \App\DB\Database(),
      new \App\Services\TokenService()
    );

    $tokenData = $authService->validateForgotLink($requestData['token']);

    $authService->setNewPassword($requestData['password'], $tokenData);

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

  $group->get('/token', function (Request $request, Response $response) {

    $jwt = TokenService::generatePublicToken();

    $apiResponse = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      "Token público gerado com sucesso",
      [
        "token"      => $jwt,
        "type"       => "Bearer",
        "expires_in" => 3600
      ]
    );

    $response->getBody()->write(json_encode($apiResponse));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($apiResponse['code']);
      
  });

});
