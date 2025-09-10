<?php

namespace App\Handlers;

use App\Utils\ApiResponseFormatter;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class ErrorHandler
{

  /**
   * Gera a resposta HTTP JSON de erro
   */
  public function __invoke(
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
  ) {

    $statusCode = (int) $exception->getCode();
    
    $statusCode = ($statusCode >= 100 && $statusCode < 600) ? $statusCode : 500;

    $apiResponse = ApiResponseFormatter::formatResponse(
      $statusCode,
      "error",
      $exception->getMessage(),
      NULL
    );

    $response = new Response();
    
    $response->getBody()->write(json_encode($apiResponse, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    return $response
      ->withStatus($statusCode)
      ->withHeader("Content-Type", "application/json");

  }

}
