<?php

use App\Models\User;
use App\Middleware\RoleMiddleware;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/users/me', function ($group) {

  $group->get('', function (Request $request, Response $response) {

    $jwt  = $request->getAttribute("jwt");

    $userId = (int)$jwt['user']->id;

    $user = User::get($userId);

    $apiResponse = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      "Dados do usuário",
      $user
    );

    $response->getBody()->write(json_encode($apiResponse));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($apiResponse['code']);

  });

  $group->put('', function (Request $request, Response $response) {

    $jwt  = $request->getAttribute("jwt");
    
    $requestData = $request->getParsedBody();

    $requestData['user_id'] = (int)$jwt['user']->id;

    $user = new User();
    
    $user->setAttributes($requestData);

    $result = $user->update();

    $apiResponse = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      "Usuário atualizado com sucesso",
      $result
    );

    $response->getBody()->write(json_encode($apiResponse));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($apiResponse['code']);
      
  });

  $group->delete('', function (Request $request, Response $response) {

    $jwt = $request->getAttribute("jwt");

    $userId = (int)$jwt['user']->id;

    User::delete($userId);

    $apiResponse = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      "Usuário excluido com sucesso",
      NULL
    );

    $response->getBody()->write(json_encode($apiResponse));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($apiResponse['code']);

  });

})->add(new RoleMiddleware(['user', 'editor', 'admin']));
