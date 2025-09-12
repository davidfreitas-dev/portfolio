<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Project;
use App\Middleware\RoleMiddleware;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

$app->get('/projects', function (Request $request, Response $response) {

  $queryParams = $request->getQueryParams();

  $page   = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
  
  $limit  = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
  
  $search = isset($queryParams['search']) ? $queryParams['search'] : "";

  $projects = Project::list($page, $limit, $search);

  $responseBody = ApiResponseFormatter::formatResponse(
    HTTPStatus::OK,
    "success",
    "Lista de Projetos",
    $projects
  );

  $response->getBody()->write(json_encode($responseBody));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($responseBody['code']);

});

$app->get('/projects/{id}', function (Request $request, Response $response, array $args) {

  $id = $args['id'];

  $project = Project::get($id);

  $responseBody = ApiResponseFormatter::formatResponse(
    HTTPStatus::OK,
    "success",
    "Detalhes do Projeto",
    $project
  );

  $response->getBody()->write(json_encode($responseBody));

  return $response
    ->withHeader('content-type', 'application/json')
    ->withStatus($responseBody['code']);

});

$app->group('/projects', function ($group) {

  $group->post('', function (Request $request, Response $response) {

    $requestData = $request->getParsedBody();

    $requestData['image'] = isset($_FILES['image']) ? $_FILES['image'] : NULL;

    $project = new Project();
    
    $project->setAttributes($requestData);

    if (!isset($requestData['id'])) {
      
      $result = $project->create();
      
      $message = "Projeto criado com sucesso";

    } else {
      
      $result = $project->update();
      
      $message = "Projeto atualizado com sucesso";
    
    }

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::OK,
      "success",
      $message,
      $result
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);

  });

  $group->delete('/{id}', function (Request $request, Response $response, array $args) {

    $id = $args['id'];

    Project::delete($id);

    $responseBody = ApiResponseFormatter::formatResponse(
      HTTPStatus::NO_CONTENT,
      "success",
      "Projeto excluído com sucesso",
      NULL
    );

    $response->getBody()->write(json_encode($responseBody));

    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($responseBody['code']);
      
  });

})->add(new RoleMiddleware("admin"));
