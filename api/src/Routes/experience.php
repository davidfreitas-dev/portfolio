<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Experience;
use App\Middleware\RoleMiddleware;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

$app->get('/experiences', function (Request $request, Response $response) {

  $queryParams = $request->getQueryParams();

  $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
  
  $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
  
  $search = isset($queryParams['search']) ? $queryParams['search'] : "";

  $experiences = Experience::list($page, $limit, $search);

  $responseBody = ApiResponseFormatter::formatResponse(
    HTTPStatus::OK,
    "success",
    "Lista de experiências",
    $experiences
  );

  $response->getBody()->write(json_encode($responseBody));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($responseBody['code']);

})->add(new RoleMiddleware(['public', 'user', 'editor', 'admin']));

$app->group('/experiences', function ($group) {

  $group->get('/{id}', function (Request $request, Response $response, array $args) {

    $id = $args['id'];

    $experience = Experience::get($id);

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      "Detalhes da experiência",
      $experience
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);

  });

  $group->post('', function (Request $request, Response $response) {

    $requestData = $request->getParsedBody();

    $experience = new Experience();

    $experience->setAttributes($requestData);

    $result = $experience->create();

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      "Experiência adicionada com sucesso",
      $result
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);

  });

  $group->put('/{id}', function (Request $request, Response $response, array $args) {

    $requestData = $request->getParsedBody();

    $requestData['id'] = (int)$args['id'];

    $experience = new Experience();

    $experience->setAttributes($requestData);

    $result = $experience->update();

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::CREATED,
      "success",
      "Experiência atualizada com sucesso",
      $result
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);

  });

  $group->delete('/{id}', function (Request $request, Response $response, array $args) {

    $id = $args['id'];

    Experience::delete($id);

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::NO_CONTENT,
      "success",
      "Experiência excluída com sucesso",
      NULL
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);

  });

})->add(new RoleMiddleware('admin'));
