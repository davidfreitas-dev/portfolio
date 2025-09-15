<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Technology;
use App\Middleware\RoleMiddleware;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

$app->get('/technologies', function (Request $request, Response $response) {

  $queryParams = $request->getQueryParams();

  $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
  
  $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
  
  $search = isset($queryParams['search']) ? $queryParams['search'] : "";

  $experiences = Technology::list($page, $limit, $search);

  $responseBody = ApiResponseFormatter::formatResponse(
    HTTPStatus::OK,
    "success",
    "Lista de Tecnologias",
    $experiences
  );

  $response->getBody()->write(json_encode($responseBody));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($responseBody['code']);

})->add(new RoleMiddleware(['public', 'user', 'editor', 'admin']));

$app->group('/technologies', function ($group) {

  $group->get('/{id}', function (Request $request, Response $response, array $args) {

    $id = $args['id'];

    $experience = Technology::get($id);

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      "Detalhes da Tecnologia",
      $experience
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);

  });

  $group->post('', function (Request $request, Response $response) {

    $requestData = $request->getParsedBody();

    $requestData['image'] = isset($_FILES['image']) ? $_FILES['image'] : NULL;

    $experience = new Technology();

    $experience->setAttributes($requestData);

    if (!isset($requestData['id'])) {

      $result = $experience->create();

    } else {

      $result = $experience->update();
      
    }

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      "Tecnologia adicionada/atualizada com sucesso",
      $result
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);

  });

  $group->delete('/{id}', function (Request $request, Response $response, array $args) {

    $id = $args['id'];

    Technology::delete($id);

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::NO_CONTENT,
      "success",
      "Tecnologia excluída com sucesso",
      NULL
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);

  });

})->add(new RoleMiddleware("admin"));